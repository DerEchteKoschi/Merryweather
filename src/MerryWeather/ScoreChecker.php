<?php

namespace App\MerryWeather;

use App\Entity\Slot;
use App\Entity\User;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ScoreChecker implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    public function __construct(private readonly int $userScoreLimit)
    {
    }

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

    public function raiseUserScore(User $user, int $toAdd = 1): bool
    {
        $hasChanged = false;
        $score = $user->getScore();
        $score+=$toAdd;
        if ($score > $this->userScoreLimit) {
            $score = $this->userScoreLimit;
        }
        if ($score !== $user->getScore()) {
            $user->setScore($score);
            $hasChanged = true;
        }
        return $hasChanged;
    }

    public function lowerUserScore(User $user, int $toSub = 1): void
    {
        $score = $user->getScore();
        $score-=$toSub;
        if ($score < 0) {
            $score = 0;
        }
        $user->setScore($score);
    }
}
