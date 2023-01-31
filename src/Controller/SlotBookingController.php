<?php

namespace App\Controller;

use App\Dto\Distribution;
use App\Entity\User;
use App\MerryWeather\BookingRuleChecker;
use App\Repository\DistributionRepository;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlotBookingController extends AbstractController
{
    #[Route('/book/{slotId}', name: 'app_slot_book')]
    public function book(int $slotId, SlotRepository $slotRepository, UserRepository $userRepository, BookingRuleChecker $bookRuleChecker): Response
    {
        $slot = $slotRepository->find($slotId);
        /** @var User $user */
        $user = $this->getUser();
        if ($slot === null) {
            $this->addFlash('danger', 'Slot nicht gefunden');
        } elseif ($slot->getUser() === null) {
            if ($bookRuleChecker->userCanBook($user, $slot)) {
                $slot->setUser($user);
                $slotRepository->save($slot, true);
                $bookRuleChecker->lowerUserScoreBySlot($user, $slot);
                $userRepository->save($user, true);
                $this->addFlash('success', 'Buchung erfolgreich');
            } else {
                $this->addFlash('warning', 'Dieser Slot ist für Sie leider nicht buchbar');
            }
        } elseif ($slot->getUser() !== $user) {
            $this->addFlash('warning', 'Es tut mir leid aber der Slot ist bereits vergeben');
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
            $this->addFlash('danger', 'Slot nicht gefunden');
        } elseif ($slot->getUser() === $user) {
            $slot->setUser(null);
            $bookRuleChecker->raiseUserScore($user, $bookRuleChecker->pointsNeededForSlot($slot));
            $userRepository->save($user, true);
            $slotRepository->save($slot, true);
            $this->addFlash('success', 'Stornierung erfolgreich');
        } elseif ($slot->getUser() !== null) {
            $this->addFlash('warning', 'Es tut mir leid aber der Slot gehört jemand anderem');
        }

        return $this->redirectToRoute('app_slots');
    }

    #[Route('/', name: 'app_slots')]
    public function index(DistributionRepository $distributionRepository): Response
    {
        $dists = Distribution::fromList($distributionRepository->findCurrentDistributions());

        return $this->render('slot_booking/index.html.twig', [
            'dists' => $dists
        ]);
    }
}
