<?php

namespace App\Twig\Runtime;

use App\Dto\Slot as SlotDto;
use App\Entity\Slot;
use App\Entity\User;
use App\MerryWeather\ScoreChecker;
use App\Repository\SlotRepository;
use Twig\Extension\RuntimeExtensionInterface;

class ScoreExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly ScoreChecker $scoreChecker, private readonly SlotRepository $slotRepository)
    {
    }

    public function canBook(User $user, Slot|SlotDto $slot): bool
    {
        if ($slot instanceof SlotDto) {
            $slot = $this->slotRepository->find($slot->id);
        }
        return $this->scoreChecker->userCanBook($user, $slot);
    }
}
