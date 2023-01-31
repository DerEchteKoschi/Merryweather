<?php

namespace App\Twig\Runtime;

use App\Dto\Slot as SlotDto;
use App\Entity\Slot;
use App\Entity\User;
use App\MerryWeather\Admin\LogMessage;
use App\MerryWeather\BookingRuleChecker;
use App\Repository\SlotRepository;
use Twig\Extension\RuntimeExtensionInterface;

class MerryWeatherExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly BookingRuleChecker $bookingRuleChecker, private readonly SlotRepository $slotRepository)
    {
    }

    public function canBook(User $user, Slot|SlotDto $slot): bool
    {
        if ($slot instanceof SlotDto) {
            $slot = $this->slotRepository->find($slot->id);
        }

        return $this->bookingRuleChecker->userCanBook($user, $slot);
    }

    public function canCancel(User $user, Slot|SlotDto $slot): bool
    {
        if ($slot instanceof SlotDto) {
            $slot = $this->slotRepository->find($slot->id);
        }

        return $this->bookingRuleChecker->userCanCancel($user, $slot);
    }

    public function bootstrapClassForLog(LogMessage $log): string
    {
        return match ($log->getLevel()) {
            100 => 'primary', //'debug',
            200 => 'info', //'info',
            250 => 'success', //'notice',
            300 => 'warning', //'warning',
            400, 500, 550, 600 => 'danger', //'error',
            //'critical',
            //'alert',
            //'emergency',
            default => '' . $log->getLevel()
        };
    }
}
