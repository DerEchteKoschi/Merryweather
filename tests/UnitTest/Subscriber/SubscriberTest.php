<?php

namespace UnitTest\Subscriber;

use App\Entity\User;
use App\EventSubscriber\LoginSubscriber;
use App\EventSubscriber\VisitSubscriber;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * @small
 * @group unitTests
 */
class SubscriberTest extends TestCase
{
    public function testLoginSubscriber()
    {
        $user = new User();
        $events = LoginSubscriber::getSubscribedEvents();
        $this->assertCount(1, $events);
        $this->assertArrayHasKey(LoginSuccessEvent::class, $events);
        $this->assertEquals('onLoginSuccessEvent', $events[LoginSuccessEvent::class]);

        $lse = $this->createMock(LoginSuccessEvent::class);
        $lse->method('getUser')->willReturn($user);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->expects($this->once())->method('save')->with($this->equalTo($user));
        $ls = new LoginSubscriber($userRepo);
        $ls->onLoginSuccessEvent($lse);

        $this->assertNotNull($user->getLastLogin());

    }

    public function testVisitSubscriber()
    {
        $user = new User();
        $events = VisitSubscriber::getSubscribedEvents();
        $this->assertCount(1, $events);
        $this->assertArrayHasKey(KernelEvents::FINISH_REQUEST, $events);
        $this->assertEquals('onKernelFinishRequest', $events[KernelEvents::FINISH_REQUEST]);

        $lse = $this->createMock(KernelEvent::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->expects($this->once())->method('save')->with($this->equalTo($user));
        $userRepo->expects($this->once())->method('isEntityManagerOpen')->willReturn(true);
        $ls = new VisitSubscriber($security, $userRepo);
        $ls->onKernelFinishRequest($lse);

        $this->assertNotNull($user->getLastVisit());

    }
}
