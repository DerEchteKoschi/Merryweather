<?php

namespace App\EventSubscriber;

use App\Controller\Admin\AdminDashboardController;
use App\Controller\Admin\DistributionCrudController;
use App\Entity\Distribution;
use App\Merryweather\BookingException;
use App\Merryweather\BookingService;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

class DistributionCancelSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly BookingService $bookingService, private readonly AdminUrlGenerator $adminUrlGenerator, private readonly RequestStack $stack)
    {
    }

    public function onBeforeEntityDeletedEvent(BeforeEntityDeletedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Distribution)) {
            return;
        }

        $error = [];
        foreach ($entity->getSlots() as $slot) {
            if ($slot->getUser() !== null) {
                try {
                    $this->bookingService->cancelSlot($slot, true);
                } catch (BookingException $exception) {
                    $error[] = implode(', ', [$slot->getText(), $exception->getMessage()]);
                }
            }
        }

        if (!empty($error)) {
            $session = $this->stack->getSession();
            if ($session instanceof FlashBagAwareSessionInterface) {
                $session->getFlashBag()->add('danger', sprintf('slot cancel for refund failed for slot(s): [%s]', implode(', ', $error)));
            }
            $event->setResponse(new RedirectResponse($this->adminUrlGenerator->setDashboard(AdminDashboardController::class)->setController(DistributionCrudController::class)->setEntityId($entity->getId())->setAction('detail')->generateUrl()));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityDeletedEvent::class => 'onBeforeEntityDeletedEvent',
        ];
    }
}
