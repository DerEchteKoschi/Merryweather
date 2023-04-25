<?php

namespace App\Events;

use App\Dto\Slot;
use Symfony\Contracts\EventDispatcher\Event;

class SlotBookedEvent extends Event implements SlotEvent
{
    public const NAME = 'slot.booked';

    public function __construct(
        protected Slot $slot,
    ) {
    }

    public function getSlot(): Slot
    {
        return $this->slot;
    }
}
