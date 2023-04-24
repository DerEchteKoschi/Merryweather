<?php

namespace App\Events;

use App\Entity\Slot;
use Symfony\Contracts\EventDispatcher\Event;

class SlotBookedEvent extends Event
{
    public const NAME = 'slot.booked';

    public function __construct(
        protected Slot $slot,
    )
    {
    }

    public function getSlot(): Slot
    {
        return $this->slot;
    }
}