<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class VisitSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Security $security, private UserRepository $userRepository)
    {
    }

    public function onKernelFinishRequest(FinishRequestEvent $event): void
    {
        $user = $this->security->getUser();
        if ($user instanceof User) {
            $visitDate = (new \DateTimeImmutable('now'))->setTime(0,0);
            if($user->getLastVisit() !== $visitDate) {
                $user->setLastVisit($visitDate);
                $this->userRepository->save($user, true);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::FINISH_REQUEST => 'onKernelFinishRequest',
        ];
    }
}
