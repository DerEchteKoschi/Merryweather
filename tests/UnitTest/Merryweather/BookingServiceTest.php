<?php

namespace UnitTest\Merryweather;
require_once(__DIR__ . '/../Test/UUIDGen.php');

use App\Entity\Distribution;
use App\Entity\Slot;
use App\Entity\User;
use App\Merryweather\AppConfig;
use App\Merryweather\BookingService;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Test\UUIDGen;

/**
 * @small
 * @group unitTests
 */
class BookingServiceTest extends TestCase
{
    use UUIDGen;
    private $appConfigMock;
    private $userRepositoryMock;
    private $securityMock;
    private $eventDispatcherMock;
    private $slotRepositoryMock;

    /**
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getSlots(): ArrayCollection
    {
        $distMock = $this->createMock(Distribution::class);
        $distMock->method('getActiveTill')->willReturn(new DateTimeImmutable());
        $distMock->method('getActiveFrom')->willReturn(new DateTimeImmutable('yesterday'));
        $slots = new ArrayCollection();
        $id = 1;
        foreach (['-10 minutes', 'now', '+10 minutes'] as $dateString) {
            $slotMock = $this->createMock(Slot::class);
            $slotMock->method('getId')->willReturn($this->genFakeUUID($id));
            $slotMock->method('getText')->willReturn('ID ' . $id++);
            $slotMock->method('getDistribution')->willReturn($distMock);
            $slotMock->method('getStartAt')->willReturn(new DateTimeImmutable($dateString));
            $slots->add($slotMock);
        }
        $distMock->method('getSlots')->willReturn($slots);
        return $slots;
    }

    protected function setUp(): void
    {
        $this->appConfigMock = $this->createMock(AppConfig::class);
        $this->appConfigMock->method('getScoreConfig')->willReturn([[3, 1]]);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->slotRepositoryMock = $this->createMock(SlotRepository::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->securityMock = $this->createMock(Security::class);
    }

    private function createBookingService()
    {
        $bookingService = new BookingService($this->appConfigMock, $this->userRepositoryMock, $this->slotRepositoryMock, $this->eventDispatcherMock, $this->securityMock);
        $bookingService->setLogger(new NullLogger());
        return $bookingService;
    }

    public function testCanBook()
    {
        $user = (new User())->setScore(2);

        $this->securityMock->method('getUser')->willReturn($user);
        $bookingService = $this->createBookingService();

        $slots = $this->getSlots();


        $this->assertFalse($bookingService->userCanBook($user, $slots->first()));
        $this->assertTrue($bookingService->userCanBook($user, $slots->last()));
        $slots->first()->method('getUser')->willReturn($user);
        $this->assertFalse($bookingService->userCanBook($user, $slots->last()));
    }

    public function testCanCancel()
    {

        $bookingService = $this->createBookingService();

        $slots = $this->getSlots();

        $user = (new User())->setScore(2);
        $this->assertFalse($bookingService->userCanCancel($user, $slots->first()));
        $this->assertFalse($bookingService->userCanCancel($user, $slots->last()));
        $slots->last()->method('getUser')->willReturn($user);
        $this->assertTrue($bookingService->userCanCancel($user, $slots->last()));
    }

    public function testBookAndCancel()
    {
        $user = $this->createMock(User::class);
        $user->method('getScore')->willReturn(2);
        $user->method('getId')->willReturn('');



        $this->securityMock->method('getUser')->willReturn($user);
        $bookingService = $this->createBookingService();

        $slots = $this->getSlots();

        $slot = $slots->last();
        $bookingService->bookSlot($slot);
        $slot->method('getUser')->willReturn($user);
        $bookingService->cancelSlot($slot);
        $this->assertTrue(true);
    }

}
