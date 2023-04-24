<?php

namespace App\EventSubscriber;

use App\Events\SlotBookedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class SlotBookedSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly HubInterface $hub)
    {

    }

    public function onSlotBooked(SlotBookedEvent $event): void
    {
        $update = new Update(
            'booking',
            json_encode(['event' => 'booked', 'slotId' => $event->getSlot()->getId()])
        );

        $return = $this->hub->publish($update);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SlotBookedEvent::NAME => 'onSlotBooked',
        ];
    }
}
