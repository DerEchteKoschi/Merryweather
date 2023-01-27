<?php

namespace App\MerryWeather;

use App\Entity\Slot;
use App\Entity\User;

class ScoreChecker
{
    public function pointsNeededForSlot(Slot $slot): int
    {
        return 0;//TODO @DerEchteKoschi
    }

    public function userCanBook(User $user, Slot $slot): bool
    {
        $hasBookedNoSlotOfDistribution = true;
        foreach ($slot->getDistribution()?->getSlots() as $checkSlot) {
            if ($checkSlot->getUser() === $user) {
                $hasBookedNoSlotOfDistribution = false;
            }
        }
        return $hasBookedNoSlotOfDistribution && $user->getScore() >= $this->pointsNeededForSlot($slot);
    }
}
