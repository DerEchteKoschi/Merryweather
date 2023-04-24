<?php

namespace App\Events;

use App\Entity\Slot;
use Symfony\Contracts\EventDispatcher\Event;

class SlotCanceledEvent extends Event
{
    public const NAME = 'slot.canceled';

    public function __construct(
        protected Slot $slot, protected bool $byAdmin = false
    )
    {
    }

    public function getSlot(): Slot
    {
        return $this->slot;
    }

    public function isByAdmin(): bool
    {
        return $this->byAdmin;
    }
}