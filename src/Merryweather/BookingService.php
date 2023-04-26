<?php

namespace App\Merryweather;

use App\Entity\Distribution;
use App\Entity\Slot;
use App\Entity\User;
use App\Events\SlotBookedEvent;
use App\Events\SlotCanceledEvent;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BookingService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly AppConfig $appConfig,
        private readonly UserRepository $userRepository,
        private readonly SlotRepository $slotRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Security $security
    ) {
    }

    /**
     * @throws BookingException
     */
    public function bookSlot(int|Slot $slot): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!($slot instanceof Slot)) {
            $slot = $this->slotRepository->find($slot);
        }
        if ($slot === null) {
            throw BookingException::slotNotFound();
        }
        if ($slot->getUser() === null) {
            try {
                $this->slotRepository->lock($slot);
                if ($this->userCanBook($user, $slot)) {
                    $slot->setUser($user);
                    $pointsPayed = $this->lowerUserScoreBySlot($user, $slot);
                    $slot->setAmountPaid($pointsPayed);
                    $this->slotRepository->save($slot, true);
                    $this->userRepository->save($user, true);
                    $this->logger->info(sprintf('User %s booked Slot %s', $user, $slot->getText()));
                    $this->eventDispatcher->dispatch(new SlotBookedEvent(\App\Dto\Slot::fromEntity($slot)), SlotBookedEvent::NAME);
                } else {
                    $this->logger->warning(sprintf('User %s tried to book Slot %s', $user, $slot->getText()));
                    throw BookingException::notBookable();
                }
            } catch (OptimisticLockException $ole) {
                throw BookingException::failed();
            }
        } elseif ($slot->getUser() !== $user) {
            throw BookingException::alreadyBooked();
        }
    }

    /**
     * @throws BookingException
     */
    public function cancelSlot(int|Slot $slot, bool $cancelByAdmin = false): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!($slot instanceof Slot)) {
            $slot = $this->slotRepository->find($slot);
        }
        if ($slot === null) {
            throw BookingException::slotNotFound();
        }
        $adminHandled = ($cancelByAdmin && $this->security->isGranted('ROLE_ADMIN'));
        if ($slot->getUser() === $user || $adminHandled) {
            $this->raiseUserScoreBySlot($slot, $adminHandled);
            $slot->setUser(null);
            $this->userRepository->save($user);
            $slot->setAmountPaid(null);
            $this->slotRepository->save($slot, true);
            $this->eventDispatcher->dispatch(new SlotCanceledEvent(\App\Dto\Slot::fromEntity($slot)), SlotCanceledEvent::NAME);
            $this->logger->info(sprintf('User %s canceled Slot %s', $user, $slot->getText()));
        } elseif ($slot->getUser() !== null) {
            $this->logger->alert(sprintf('User %s tried to cancel Slot %s that belongs to %s', $user, $slot->getText(), $slot->getUser()));
            throw BookingException::slotNotYours();
        }
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

    private function lowerUserScoreBySlot(User $user, Slot $slot): int
    {
        $pointsNeeded = $this->pointsNeededForSlot($slot);
        $this->lowerUserScore($user, $pointsNeeded);

        return $pointsNeeded;
    }

    public function pointsNeededForSlot(Slot $slot, string $day = 'today'): int
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

    private function raiseUserScoreBySlot(Slot $slot, bool $refund = false): void
    {
        $this->raiseUserScore($slot->getUser(), $refund ? ($slot->getAmountPaid() ?? $this->pointsNeededForSlot($slot)) : $this->pointsNeededForSlot($slot));
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
    private function hasBookednoOtherSlotOfSameDistribution(Slot $slot, User $user): bool
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
     * @param int[][] $dayConfig
     * @param int     $range
     * @param int     $day
     * @return int[]
     */
    private static function configNeededForDay(array $dayConfig, int $range, int $day): array
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
     * @param int[][] $scoreConfig
     * @param int     $slotPosition
     * @param int     $totalNoOfSlots
     * @param int     $dayIndex
     * @param int     $dayCount
     * @return int
     */
    private static function getCost(array $scoreConfig, int $slotPosition, int $totalNoOfSlots, int $dayIndex, int $dayCount): int
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
