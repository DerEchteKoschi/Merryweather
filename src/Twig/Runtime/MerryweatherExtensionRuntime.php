<?php

namespace App\Twig\Runtime;

use App\Dto\Slot as SlotDto;
use App\Entity\Slot;
use App\Entity\User;
use App\Merryweather\Admin\LogMessage;
use App\Merryweather\AppConfig;
use App\Merryweather\BookingRuleChecker;
use App\Repository\SlotRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class MerryweatherExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly BookingRuleChecker $bookingRuleChecker,
        private readonly SlotRepository $slotRepository,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly AppConfig $appConfig,
        private readonly array $supportedLocales
    ) {
    }

    public function canBook(Slot|SlotDto $slot): bool
    {
        if ($slot instanceof SlotDto) {
            $slot = $this->slotRepository->find($slot->id);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        return $this->bookingRuleChecker->userCanBook($user, $slot);
    }

    public function slotCost(Slot|SlotDto $slot): string
    {
        if (!$this->security->isGranted('ROLE_ADMIN') || !$this->appConfig->isAdminShowPoints()) {
            return '';
        }

        if ($slot instanceof SlotDto) {
            $slot = $this->slotRepository->find($slot->id);
        }

        return $this->translator->trans('admin_slot_cost', ['score' => $this->bookingRuleChecker->pointsNeededForSlot($slot)]);
    }

    public function userScore(): string
    {
        if (!$this->security->isGranted('ROLE_ADMIN') || !$this->appConfig->isAdminShowPoints()) {
            return '';
        }

        /** @var User $user */
        $user = $this->security->getUser();

        return $this->translator->trans('my_score', ['score' => $user->getScore()]);
    }



    public function canCancel(Slot|SlotDto $slot): bool
    {
        if ($slot instanceof SlotDto) {
            $slot = $this->slotRepository->find($slot->id);
        }

        /** @var User $user */
        $user = $this->security->getUser();

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
