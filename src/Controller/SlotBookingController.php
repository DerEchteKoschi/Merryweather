<?php

namespace App\Controller;

use App\Dto\Distribution;
use App\Entity\User;
use App\Merryweather\BookingRuleChecker;
use App\Repository\DistributionRepository;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}')]
class SlotBookingController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Route('/book/{slotId}', name: 'app_slot_book')]
    public function book(int $slotId, SlotRepository $slotRepository, UserRepository $userRepository, BookingRuleChecker $bookRuleChecker): Response
    {
        $slot = $slotRepository->find($slotId);
        /** @var User $user */
        $user = $this->getUser();
        if ($slot === null) {
            $this->addFlash('danger', $this->translator->trans('slot_not_found'));
        } elseif ($slot->getUser() === null) {
            try {
                $slotRepository->lock($slot);
                if ($bookRuleChecker->userCanBook($user, $slot)) {
                    $slot->setUser($user);
                    $slotRepository->save($slot, true);
                    $bookRuleChecker->lowerUserScoreBySlot($user, $slot);
                    $userRepository->save($user, true);
                    $this->addFlash('success', $this->translator->trans('booking_successful'));
                } else {
                    $this->addFlash('warning', $this->translator->trans('slot_not_bookable'));
                }
            } catch (OptimisticLockException $ole) {
                $this->addFlash('warning', $this->translator->trans('booking_failed'));
            }
        } elseif ($slot->getUser() !== $user) {
            $this->addFlash('warning', $this->translator->trans('slot_already_booked'));
        }

        return $this->redirectToRoute('app_slots');
    }

    #[Route('/cancel/{slotId}', name: 'app_slot_cancel')]
    public function cancel(int $slotId, SlotRepository $slotRepository, UserRepository $userRepository, BookingRuleChecker $bookRuleChecker): Response
    {
        $slot = $slotRepository->find($slotId);
        /** @var User $user */
        $user = $this->getUser();
        if ($slot === null) {
            $this->addFlash('danger', $this->translator->trans('slot_not_found'));
        } elseif ($slot->getUser() === $user) {
            $slot->setUser(null);
            $bookRuleChecker->raiseUserScoreBySlot($user, $slot);
            $userRepository->save($user, true);
            $slotRepository->save($slot, true);
            $this->addFlash('success', 'Stornierung erfolgreich');
        } elseif ($slot->getUser() !== null) {
            $this->addFlash('warning', $this->translator->trans('slot_not_yours'));
        }

        return $this->redirectToRoute('app_slots');
    }

    #[Route('/slots', name: 'app_slots')]
    public function index(DistributionRepository $distributionRepository): Response
    {
        $dists = Distribution::fromList($distributionRepository->findCurrentDistributions());

        return $this->render('slot_booking/index.html.twig', [
            'dists' => $dists
        ]);
    }
}
