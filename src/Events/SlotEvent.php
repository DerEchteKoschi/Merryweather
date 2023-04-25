<?php

namespace App\Events;

use App\Dto\Slot;

interface SlotEvent
{
    public function getSlot(): Slot;
}