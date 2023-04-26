<?php

namespace UnitTest\Merryweather;

use App\Entity\Distribution;
use App\Entity\Slot;
use App\Entity\User;
use App\Merryweather\AppConfig;
use App\Merryweather\BookingService;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @small
 * @group unitTests
 */
class BookingRuleCheckerTest extends TestCase
{
    public function slotData()
    {
        yield [[[]], 3, [0, 0, 0]];
        yield [[[3]], 3, [3, 3, 3]];
        yield [[[3, 2]], 3, [3, 3, 2]];
        yield [[[3, 2]], 5, [3, 3, 3, 2, 2]];
        yield [[[3, 2]], 1, [3]];
        yield [[[30, 2]], 1, [20]];
        yield [[[4,2],[2, 0]], 1, [4]];
        yield [[[4,2],[2, 0]], 1, [2], '4 days ago'];
    }

    public function testCanBook()
    {
        $cfgMock = $this->createMock(AppConfig::class);
        $cfgMock->method('getScoreConfig')->willReturn([[3, 1]]);
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $brc = new BookingService($cfgMock, $userRepositoryMock);
        $brc->setLogger($this->createMock(LoggerInterface::class));

        $distMock = $this->createMock(Distribution::class);
        $distMock->method('getActiveTill')->willReturn(new DateTimeImmutable());
        $distMock->method('getActiveFrom')->willReturn(new DateTimeImmutable('yesterday'));
        $slots = new ArrayCollection();
        foreach (['-10 minutes', 'now', '+10 minutes'] as $dateString) {
            $slotMock = $this->createMock(Slot::class);
            $slotMock->method('getDistribution')->willReturn($distMock);
            $slotMock->method('getStartAt')->willReturn(new DateTimeImmutable($dateString));
            $slots->add($slotMock);
        }
        $distMock->method('getSlots')->willReturn($slots);

        $user = (new User())->setScore(2);
        $this->assertFalse($brc->userCanBook($user, $slots->first()));
        $this->assertTrue($brc->userCanBook($user, $slots->last()));
        $slots->first()->method('getUser')->willReturn($user);
        $this->assertFalse($brc->userCanBook($user, $slots->last()));
    }

    public function testCanCancel()
    {
        $cfgMock = $this->createMock(AppConfig::class);
        $cfgMock->method('getScoreConfig')->willReturn([[3, 1]]);
        $userRepositoryMock = $this->createMock(UserRepository::class);

        $brc = new BookingService($cfgMock, $userRepositoryMock);
        $brc->setLogger($this->createMock(LoggerInterface::class));

        $distMock = $this->createMock(Distribution::class);
        $distMock->method('getActiveTill')->willReturn(new DateTimeImmutable());
        $slots = new ArrayCollection();
        foreach (['-10 minutes', 'now', '+10 minutes'] as $dateString) {
            $slotMock = $this->createMock(Slot::class);
            $slotMock->method('getDistribution')->willReturn($distMock);
            $slotMock->method('getStartAt')->willReturn(new DateTimeImmutable($dateString));
            $slots->add($slotMock);
        }
        $distMock->method('getSlots')->willReturn($slots);

        $user = (new User())->setScore(2);
        $this->assertFalse($brc->userCanCancel($user, $slots->first()));
        $this->assertFalse($brc->userCanCancel($user, $slots->last()));
        $slots->last()->method('getUser')->willReturn($user);
        $this->assertTrue($brc->userCanCancel($user, $slots->last()));
    }

    /**
     * @dataProvider slotData
     */
    public function testRaiseAndLower($slotCfg, $maxSlots, $costs, $from='yesterday', $till='today')
    {
        $cfgMock = $this->createMock(AppConfig::class);
        $cfgMock->method('getScoreConfig')->willReturn($slotCfg);
        $cfgMock->method('getScoreLimit')->willReturn(20);
        $userRepositoryMock = $this->createMock(UserRepository::class);

        $brc = new BookingService($cfgMock, $userRepositoryMock);
        $brc->setLogger($this->createMock(LoggerInterface::class));

        $distMock = $this->createMock(Distribution::class);
        $distMock->method('getActiveTill')->willReturn(new DateTimeImmutable($till));
        $distMock->method('getActiveFrom')->willReturn(new DateTimeImmutable($from));

        $slots = new ArrayCollection();
        for ($i = 0; $i < $maxSlots; $i++) {
            $slotMock = $this->createMock(Slot::class);
            $slotMock->method('getDistribution')->willReturn($distMock);
            $slots->add($slotMock);
        }
        $distMock->method('getSlots')->willReturn($slots);

        foreach ($slots as $idx => $slot) {
            $user = (new User())->setScore(0);
            $brc->raiseUserScoreBySlot($user, $slot);
            $this->assertEquals($costs[$idx], $user->getScore());
            $brc->lowerUserScoreBySlot($user, $slot);
            $this->assertEquals(0, $user->getScore());
        }
    }

}
