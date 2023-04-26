<?php

namespace UnitTest\Controller;

use App\Controller\SlotBookingController;
use App\Entity\Slot;
use App\Entity\User;
use App\Merryweather\BookingService;
use App\Repository\DistributionRepository;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @small
 * @group unitTests
 */
class SlotBookingControllerTest extends TestCase
{

    public function bookData()
    {
        //@todo fix yield [(new Slot())->setStartAt(new \DateTimeImmutable())];
        yield [new Slot(), false];
        yield [null, false];
        yield [(new Slot())->setUser((new User())->setPhone('123'))];
        yield [new Slot(), true, true];
    }

    public function cancelData()
    {
        yield [(new Slot())->setStartAt(new \DateTimeImmutable())];
        yield [new Slot(), false];
        yield [null, false];
       //@todo fix yield [(new Slot())->setUser((new User())->setPhone('123'))->setStartAt(new \DateTimeImmutable())];
        yield [(new Slot())->setUser((new User())->setPhone('321')), true, true];
    }

    /**
     * @param $slot
     * @param $bookable
     * @dataProvider bookData
     */
    public function testBook($slot, $bookable = true, $throwOle = false)
    {
        $routerMock = $this->createMock(Router::class);
        $routerMock->method('generate')->willReturnArgument(0);
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->once())->method('getFlashBag')->willReturn(new FlashBag());
        $stackMock = $this->createMock(RequestStack::class);
        $stackMock->method('getSession')->willReturn($sessionMock);

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->method('getUser')->willReturn(new User());

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        $containerMock = $this->createMock(Container::class);
        $containerMock->method('has')->willReturn(true);
        $containerMock->method('get')->willReturnCallback(function ($string) use ($stackMock, $tokenStorageMock, $routerMock) {
            $mocks = [
                'request_stack' => $stackMock,
                'security.token_storage' => $tokenStorageMock,
                'router' => $routerMock
            ];

            return $mocks[$string];
        });

        $slotRepositoryMock = $this->createMock(SlotRepository::class);
        $slotRepositoryMock->method('find')->willReturn($slot);
        if ($throwOle) {
            $slotRepositoryMock->method('lock')->willThrowException(new OptimisticLockException('', null));
        }

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $bookingServiceMock = $this->createMock(BookingService::class);
        $bookingServiceMock->method('userCanBook')->willReturn($bookable);
        $bookingServiceMock->setLogger(new NullLogger());
        $eventDp = $this->createMock(EventDispatcherInterface::class);

        $controller = new SlotBookingController($this->translatorMock(), $bookingServiceMock);
        $controller->setContainer($containerMock);
        $controller->setLogger(new NullLogger());
        $response = $controller->book(1, );
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('app_slots', $response->getTargetUrl());
    }

    /**
     * @param      $slot
     * @param bool $cancellable
     * @dataProvider cancelData
     */
    public function testCancel(?Slot $slot, bool $cancellable = true, $someoneElse = false)
    {
        $routerMock = $this->createMock(Router::class);
        $routerMock->method('generate')->willReturnArgument(0);
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getFlashBag')->willReturn(new FlashBag());
        $stackMock = $this->createMock(RequestStack::class);
        $stackMock->method('getSession')->willReturn($sessionMock);

        $tokenMock = $this->createMock(TokenInterface::class);
        if ($someoneElse) {
            $tokenMock->method('getUser')->willReturn(new User());
        } else {
            $tokenMock->method('getUser')->willReturn($slot?->getUser() ?? new User());
        }

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        $containerMock = $this->createMock(Container::class);
        $containerMock->method('has')->willReturn(true);
        $containerMock->method('get')->willReturnCallback(function ($string) use ($stackMock, $tokenStorageMock, $routerMock) {
            $mocks = [
                'request_stack' => $stackMock,
                'security.token_storage' => $tokenStorageMock,
                'router' => $routerMock
            ];

            return $mocks[$string];
        });

        $slotRepositoryMock = $this->createMock(SlotRepository::class);
        $slotRepositoryMock->method('find')->willReturn($slot);

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $bookingRuleCheckerMock = $this->createMock(BookingService::class);
        $bookingRuleCheckerMock->method('userCanCancel')->willReturn($cancellable);
        $bookingRuleCheckerMock->setLogger(new NullLogger());
        $eventDp = $this->createMock(EventDispatcherInterface::class);

        $controller = new SlotBookingController($this->translatorMock(), $bookingRuleCheckerMock);
        $controller->setContainer($containerMock);
        $controller->setLogger(new NullLogger());

        $response = $controller->cancel(1);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('app_slots', $response->getTargetUrl());
    }

    public function testIndex()
    {
        $twigMock = $this->createMock(Environment::class);
        $twigMock->method('render')->with($this->isType('string'), $this->equalTo(['dists' => []]))->willReturn('done');
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('has')->willReturn(true);
        $containerMock->method('get')->willReturnCallback(function ($string) use ($twigMock) {
            $mocks = [
                'twig' => $twigMock,
            ];

            return $mocks[$string];
        });

        $distributionRepositoryMock = $this->createMock(DistributionRepository::class);
        $distributionRepositoryMock->method('findCurrentDistributions')->willReturn([]);
        $bookingRuleCheckerMock = $this->createMock(BookingService::class);

        $controller = new SlotBookingController($this->translatorMock(), $bookingRuleCheckerMock);
        $controller->setContainer($containerMock);
        $response = $controller->index($distributionRepositoryMock);
        $this->assertEquals('done', $response->getContent());
    }

    private function translatorMock()
    {
        $mock = $this->createMock(TranslatorInterface::class);
        $mock->method('trans')->willReturnArgument(0);

        return $mock;
    }
}
