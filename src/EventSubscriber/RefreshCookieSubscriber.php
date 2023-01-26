<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RefreshCookieSubscriber implements EventSubscriberInterface
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        if ($session->isStarted()) {
            $response = $event->getResponse();
            $lifetime = (int)ini_get('session.cookie_lifetime');
            $cookie = new Cookie($session->getName(), $session->getId(), time() + $lifetime);
            $response->headers->setCookie($cookie);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
