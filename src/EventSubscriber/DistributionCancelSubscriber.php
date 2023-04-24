<?php

namespace App\EventSubscriber;

use App\Entity\Distribution;
use App\Merryweather\BookingRuleChecker;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DistributionCancelSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly BookingRuleChecker $bookingRuleChecker)
    {
    }

    public function onBeforeEntityDeletedEvent(BeforeEntityDeletedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Distribution)) {
            return;
        }

        foreach ($entity->getSlots() as $slot) {
            if ($slot->getUser() !== null) {
                $this->bookingRuleChecker->raiseUserScoreBySlot($slot->getUser(), $slot, true);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityDeletedEvent::class => 'onBeforeEntityDeletedEvent',
        ];
    }
}
