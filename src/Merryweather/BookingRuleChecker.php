<?php

namespace App\Merryweather;

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

    public function lowerUserScoreBySlot(User $user, Slot $slot): void
    {
        $this->lowerUserScore($user, $this->pointsNeededForSlot($slot));
    }

    public function raiseUserScoreBySlot(User $user, Slot $slot): void
    {
        $this->raiseUserScore($user, $this->pointsNeededForSlot($slot));
    }

    public function pointsNeededForSlot(Slot $slot): int
    {
        $scoreConfig = $this->appConfig->getScoreConfig();
        $scoreConfigCount = count($scoreConfig);
        if ($scoreConfigCount === 0) {
            return 0;
        }
        $totalNoOfSlots = $slot->getDistribution()->getSlots()->count();
        $slotPosition = 0;
        $cost = 0;
        foreach ($slot->getDistribution()->getSlots() as $slotFromList) {
            if ($slot === $slotFromList) {
                break;
            }
            $slotPosition++;
        }

        if ($scoreConfigCount >= $totalNoOfSlots) {
            return $scoreConfig[$slotPosition];
        }

        $factor = $scoreConfigCount / $totalNoOfSlots;
        return $scoreConfig[(int)($factor * $slotPosition)];
    }

    public function userCanBook(User $user, Slot $slot): bool
    {
        return ($slot->getDistribution() !== null)
               && !$this->isSlotInPast($slot)
               && ($slot->getUser() === null)
               && $this->hasBookednoOtherSlotOfSameDistribution($slot, $user)
               && $user->getScore() >= $this->pointsNeededForSlot($slot);
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

    public function lowerUserScore(User $user, int $toSub = 1): void
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
