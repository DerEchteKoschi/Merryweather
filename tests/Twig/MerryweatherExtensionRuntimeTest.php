<?php

namespace tests\Twig;

use App\Entity\Slot;
use App\Entity\User;
use App\MerryWeather\Admin\LogMessage;
use App\MerryWeather\BookingRuleChecker;
use App\Repository\SlotRepository;
use App\Twig\Runtime\MerryWeatherExtensionRuntime;
use PHPUnit\Framework\TestCase;

class MerryweatherExtensionRuntimeTest extends TestCase
{
    public function testCanBook()
    {
        [$slotE, $mer, $user] = $this->prepare('userCanBook');
        $this->assertTrue($mer->canBook($user, \App\Dto\Slot::fromEntity($slotE)));
    }

    public function testCanCncel()
    {
        [$slotE, $mer, $user] = $this->prepare('userCanCancel');
        $this->assertTrue($mer->canCancel($user, \App\Dto\Slot::fromEntity($slotE)));
    }

    public function testSlotCost()
    {
        $slot = $this->getSlotMock();

        $bookingRuleChecker = $this->createMock(BookingRuleChecker::class);
        $bookingRuleChecker->method('pointsNeededForSlot')->with($this->equalTo($slot))->willReturn(815);

        $slotRepository = $this->createMock(SlotRepository::class);
        $slotRepository->method('find')->willReturn($slot);
        $merryweatherExtensionRuntime = new MerryWeatherExtensionRuntime($bookingRuleChecker, $slotRepository);
        $this->assertEquals(815, $merryweatherExtensionRuntime->slotCost(\App\Dto\Slot::fromEntity($slot)));
    }

    /**
     * @dataProvider logTestProvider
     */
    public function testBootstrapClassForLog(string $expected, LogMessage $log)
    {
        $merryweatherExtensionRuntime = new MerryWeatherExtensionRuntime($this->createMock(BookingRuleChecker::class), $this->createMock(SlotRepository::class));
        $class = $merryweatherExtensionRuntime->bootstrapClassForLog($log);
        $this->assertEquals($expected, $class);
    }

    public function logTestProvider(): \Generator
    {
        $dt = new \DateTimeImmutable('now');

        yield ['primary', new LogMessage('', '', 100, '', $dt)];
        yield ['info', new LogMessage('', '', 200, '', $dt)];
        yield ['success', new LogMessage('', '', 250, '', $dt)];
        yield ['warning', new LogMessage('', '', 300, '', $dt)];
        yield ['danger', new LogMessage('', '', 400, '', $dt)];
        yield ['danger', new LogMessage('', '', 500, '', $dt)];
        yield ['danger', new LogMessage('', '', 550, '', $dt)];
        yield ['danger', new LogMessage('', '', 600, '', $dt)];
        yield ['99', new LogMessage('', '', 99, '', $dt)];

    }

    /**
     * @return Slot|(Slot&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getSlotMock(): Slot|\PHPUnit\Framework\MockObject\MockObject
    {
        $slot = $this->createMock(Slot::class);
        $slot->method('getId')->willReturn(1);
        $slot->method('getUser')->willReturn(null);
        $slot->method('getStartAt')->willReturn(new \DateTimeImmutable('01.01.2023 00:00'));
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
        $slotRepository = $this->createMock(SlotRepository::class);
        $slotRepository->method('find')->willReturn($slot);
        $merryweatherExtensionRuntime = new MerryWeatherExtensionRuntime($bookingRuleChecker, $slotRepository);
        $user = new User();
        $user->setDisplayName('test');
        $bookingRuleChecker->method($method)->with($this->equalTo($user), $this->equalTo($slot))->willReturn(true);

        return [$slot, $merryweatherExtensionRuntime, $user];
    }

}
