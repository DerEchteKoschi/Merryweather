<?php

namespace App\EventSubscriber;

use App\Events\SlotCanceledEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class SlotCanceledSubscriber implements EventSubscriberInterface
{

    public function __construct(private readonly HubInterface $hub)
    {

    }
    public function onSlotCanceled(SlotCanceledEvent $event): void
    {
        $update = new Update(
            'booking',
            json_encode(['event' => 'canceled', 'slotId' => $event->getSlot()->getId()])
        );

        $return = $this->hub->publish($update);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SlotCanceledEvent::NAME => 'onSlotCanceled',
        ];
    }
}
