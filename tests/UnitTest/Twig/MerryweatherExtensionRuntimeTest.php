<?php

namespace UnitTest\Twig;

use App\Entity\Slot;
use App\Entity\User;
use App\Merryweather\Admin\LogMessage;
use App\Merryweather\AppConfig;
use App\Merryweather\BookingRuleChecker;
use App\Repository\SlotRepository;
use App\Twig\Runtime\MerryweatherExtensionRuntime;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @small
 * @group unitTests
 */
class MerryweatherExtensionRuntimeTest extends TestCase
{
    public function logTestProvider(): Generator
    {
        $dt = new DateTimeImmutable('now');

        yield 'loglevel 100' => ['primary', new LogMessage('', '', 100, '', $dt)];
        yield 'loglevel 200' => ['info', new LogMessage('', '', 200, '', $dt)];
        yield 'loglevel 250' => ['success', new LogMessage('', '', 250, '', $dt)];
        yield 'loglevel 300' => ['warning', new LogMessage('', '', 300, '', $dt)];
        yield 'loglevel 400' => ['danger', new LogMessage('', '', 400, '', $dt)];
        yield 'loglevel 500' => ['danger', new LogMessage('', '', 500, '', $dt)];
        yield 'loglevel 550' => ['danger', new LogMessage('', '', 550, '', $dt)];
        yield 'loglevel 600' => ['danger', new LogMessage('', '', 600, '', $dt)];
        yield 'loglevel 99 (unkown level)' => ['99', new LogMessage('', '', 99, '', $dt)];

    }

    public function scoreMatrix()
    {
        yield [true, true, '815'];
        yield [true, false, ''];
        yield [false, false, ''];
        yield [false, true, ''];
    }

    /**
     * @dataProvider logTestProvider
     */
    public function testBootstrapClassForLogMessage(string $expected, LogMessage $log)
    {
        $merryweatherExtensionRuntime = $this->newMerryweatherExtensionRuntime();
        $class = $merryweatherExtensionRuntime->bootstrapClassForLog($log);
        $this->assertEquals($expected, $class);
    }

    public function testCanBook()
    {
        /** @var $mer MerryweatherExtensionRuntime */
        [$slotE, $mer] = $this->prepare('userCanBook');
        $this->assertTrue($mer->canBook(\App\Dto\Slot::fromEntity($slotE)));
    }

    public function testCanCncel()
    {
        /** @var $mer MerryweatherExtensionRuntime */
        [$slotE, $mer] = $this->prepare('userCanCancel');
        $this->assertTrue($mer->canCancel(\App\Dto\Slot::fromEntity($slotE)));
    }

    /**
     * @param $isAdmin
     * @param $isActive
     * @dataProvider scoreMatrix
     *
     */
    public function testSlotCost($isAdmin, $isActive, $expected)
    {
        $slot = $this->getSlotMock();

        $bookingRuleChecker = $this->createMock(BookingRuleChecker::class);
        $bookingRuleChecker->method('pointsNeededForSlot')->with($this->equalTo($slot))->willReturn(815);

        $securityMock = $this->createMock(Security::class);
        $securityMock->method('isGranted')->willReturnCallback(function ($param) use ($isAdmin) {
            $result = ($param === 'ROLE_ADMIN') && $isAdmin;

            return $result;
        });
        $translatorMock = $this->createMock(TranslatorInterface::class);
        $translatorMock->method('trans')->willReturn('815');
        $appConfigMock = $this->createMock(AppConfig::class);
        $appConfigMock->method('isAdminShowPoints')->willReturn($isActive);
        $merryweatherExtensionRuntime = $this->newMerryweatherExtensionRuntime(slotMock: $slot, bookingRuleChecker: $bookingRuleChecker, securityMock: $securityMock, translatorMock: $translatorMock,
            appConfigMock: $appConfigMock);
        $this->assertEquals($expected, $merryweatherExtensionRuntime->slotCost(\App\Dto\Slot::fromEntity($slot)));
    }

    /**
     * @param $isAdmin
     * @param $isActive
     * @param $expected
     * @dataProvider scoreMatrix
     */
    public function testUserScore($isAdmin, $isActive, $expected)
    {
        $slot = $this->getSlotMock();

        $bookingRuleChecker = $this->createMock(BookingRuleChecker::class);
        $bookingRuleChecker->method('pointsNeededForSlot')->with($this->equalTo($slot))->willReturn(815);

        $securityMock = $this->createMock(Security::class);
        $securityMock->method('getUser')->willReturn((new User())->setScore(1));
        $securityMock->method('isGranted')->willReturnCallback(function ($param) use ($isAdmin) {
            $result = ($param === 'ROLE_ADMIN') && $isAdmin;

            return $result;
        });
        $translatorMock = $this->createMock(TranslatorInterface::class);
        $translatorMock->method('trans')->willReturn('815');
        $appConfigMock = $this->createMock(AppConfig::class);
        $appConfigMock->method('isAdminShowPoints')->willReturn($isActive);
        $merryweatherExtensionRuntime = $this->newMerryweatherExtensionRuntime(slotMock: $slot, bookingRuleChecker: $bookingRuleChecker, securityMock: $securityMock, translatorMock: $translatorMock,
            appConfigMock: $appConfigMock);
        $this->assertEquals($expected, $merryweatherExtensionRuntime->userScore(\App\Dto\Slot::fromEntity($slot)));
    }

    /**
     * @return Slot|(Slot&MockObject)|MockObject
     */
    protected function getSlotMock(): Slot|MockObject
    {
        $slot = $this->createMock(Slot::class);
        $slot->method('getId')->willReturn(1);
        $slot->method('getUser')->willReturn(null);
        $slot->method('getStartAt')->willReturn(new DateTimeImmutable('01.01.2023 00:00'));
        $slot->method('getText')->willReturn('text');

        return $slot;
    }


    /**
     * @return array
     */
    protected function prepare($method): array
    {
        $bookingRuleChecker = $this->createMock(BookingRuleChecker::class);
        $slot = $this->getSlotMock();
        $bookingRuleChecker->method($method)->with($this->isInstanceOf(User::class), $this->equalTo($slot))->willReturn(true);
        $merryweatherExtensionRuntime = $this->newMerryweatherExtensionRuntime(slotMock: $slot, bookingRuleChecker: $bookingRuleChecker);

        return [$slot, $merryweatherExtensionRuntime];
    }

    private function newMerryweatherExtensionRuntime(
        $slotMock = null,
        $bookingRuleChecker = null,
        $slotRepository = null,
        $securityMock = null,
        $translatorMock = null,
        $appConfigMock = null
    ): MerryweatherExtensionRuntime {
        if ($slotMock === null) {
            $slotMock = $this->getSlotMock();
        }

        if ($bookingRuleChecker === null) {
            $bookingRuleChecker = $this->createMock(BookingRuleChecker::class);
            $bookingRuleChecker->method('pointsNeededForSlot')->with($this->equalTo($slotMock))->willReturn(815);
        }

        if ($slotRepository === null) {
            $slotRepository = $this->createMock(SlotRepository::class);
            $slotRepository->method('find')->willReturn($slotMock);
        }

        if ($securityMock === null) {
            $securityMock = $this->createMock(Security::class);
            $securityMock->method('getUser')->willReturn(new User());

        }
        if ($translatorMock === null) {
            $translatorMock = $this->createMock(TranslatorInterface::class);
        }
        if ($appConfigMock === null) {
            $appConfigMock = $this->createMock(AppConfig::class);
        }

        return new MerryweatherExtensionRuntime($bookingRuleChecker, $slotRepository, $securityMock, $translatorMock, $appConfigMock, []);

    }

}
