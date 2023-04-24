<?php

namespace App\Merryweather;

use App\Entity\Distribution;
use App\Entity\Slot;
use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class BookingRuleChecker implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly AppConfig $appConfig, private readonly UserRepository $userRepository)
    {
    }

    private static function getCost($scoreConfig, $slotPosition, $totalNoOfSlots, $dayIndex, $dayCount)
    {
        $scoreConfigForDay = self::configNeededForDay($scoreConfig, $dayCount, $dayIndex);
        $scoreConfigCount = count($scoreConfigForDay);
        if ($scoreConfigCount === 0) {
            return 0;
        }
        if ($scoreConfigCount >= $totalNoOfSlots) {
            return $scoreConfigForDay[$slotPosition];
        }

        $factor = $scoreConfigCount / $totalNoOfSlots;

        return $scoreConfigForDay[(int)($factor * $slotPosition)];
    }

    private function lowerUserScore(User $user, int $toSub = 1): void
    {
        $previousScore = $score = $user->getScore();
        $score -= $toSub;
        if ($score < 0) {
            $score = 0;
        }
        $user->setScore($score);
        $this->userRepository->save($user, true);
        $this->logger->info(sprintf('lowered score for user [%s] from %d to %d', $user, $previousScore, $score));

    }

    public function lowerUserScoreBySlot(User $user, Slot $slot): int
    {
        $pointsNeeded = $this->pointsNeededForSlot($slot);
        $this->lowerUserScore($user, $pointsNeeded);

        return $pointsNeeded;
    }

    public function pointsNeededForSlot(Slot $slot, $day = 'today'): int
    {
        /** @var Distribution $distribution */
        $distribution = $slot->getDistribution();
        $today = new \DateTimeImmutable($day);
        $days = $distribution->getActiveTill()->diff($distribution->getActiveFrom())->d;
        if ($today <= $distribution->getActiveFrom()) {
            $dayIndex = 0;
        } elseif ($today > $distribution->getActiveTill()) {
            $dayIndex = $days - 1;
        } else {
            $dayIndex = $today->diff($distribution->getActiveFrom())->d;
        }

        $slotPosition = 0;
        foreach ($distribution->getSlots() as $slotFromList) {
            if ($slot === $slotFromList) {
                break;
            }
            $slotPosition++;
        }
        $totalNoOfSlots = $distribution->getSlots()->count();

        return self::getCost($this->appConfig->getScoreConfig(), $slotPosition, $totalNoOfSlots, $dayIndex, $days);

    }

    public function raiseUserScore(User $user, int $toAdd = 1): bool
    {
        $hasChanged = false;
        $previousScore = $score = $user->getScore();
        $score += $toAdd;
        if ($score > $this->appConfig->getScoreLimit()) {
            $score = $this->appConfig->getScoreLimit();
        }
        if ($score !== $user->getScore()) {
            $user->setScore($score);
            $hasChanged = true;
            $this->userRepository->save($user, true);
            $this->logger->info(sprintf('raised score for user [%s] from %d to %d', $user, $previousScore, $score));
        }
        return $hasChanged;
    }

    public function raiseUserScoreBySlot(User $user, Slot $slot): void
    {
        $this->raiseUserScore($user, $slot->getAmountPaid() ?? $this->pointsNeededForSlot($slot));
    }

    public function userCanBook(User $user, Slot $slot): bool
    {
        return ($slot->getDistribution() !== null)
               && !$this->isSlotInPast($slot)
               && ($slot->getUser() === null)
               && $this->hasBookednoOtherSlotOfSameDistribution($slot, $user)
               && $user->getScore() >= $this->pointsNeededForSlot($slot);
    }

    public function userCanCancel(User $user, Slot $slot): bool
    {
        return ($slot->getDistribution() !== null)
               && !$this->isSlotInPast($slot)
               && ($slot->getUser() === $user);
    }

    /**
     * @param Slot $slot
     * @param User $user
     * @return bool
     */
    protected function hasBookednoOtherSlotOfSameDistribution(Slot $slot, User $user): bool
    {
        $hasBookedNoSlotOfDistribution = true;
        foreach ($slot->getDistribution()->getSlots() as $checkSlot) {
            if ($checkSlot->getUser() === $user) {
                $hasBookedNoSlotOfDistribution = false;
            }
        }

        return $hasBookedNoSlotOfDistribution;
    }

    private static function configNeededForDay($dayConfig, $range, $day)
    {
        if (count($dayConfig) === 1) {
            return $dayConfig[0];
        }

        if (count($dayConfig) >= $range) {
            return $dayConfig[$day - 1];
        }

        $factor = count($dayConfig) / $range;

        return $dayConfig[(int)($factor * ($day - 1))];
    }

    /**
     * @param Slot $slot
     * @return bool
     */
    private function isSlotInPast(Slot $slot): bool
    {
        /** @var \DateTimeImmutable $distributionDay */
        $distributionDay = $slot->getDistribution()->getActiveTill();
        $slotDateTime = $distributionDay->setTime((int)$slot->getStartAt()->format('H'), (int)$slot->getStartAt()->format('i'));

        return $slotDateTime < new \DateTimeImmutable();
    }
}
