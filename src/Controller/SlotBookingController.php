<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DistributionRepository;
use App\Repository\SlotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlotBookingController extends AbstractController
{
    #[Route('/book/{slotId}', name: 'app_slot_book')]
    public function book(int $slotId, SlotRepository $slotRepository): Response
    {
        //TODO introduce ScoreSystem
        $slot = $slotRepository->find($slotId);
        /** @var User $user */
        $user = $this->getUser();
        if ($slot === null) {
            $this->addFlash('danger', 'Slot nicht gefunden');
        } elseif ($slot->getUser() === null) {
            $slot->setUser($user);
            $slotRepository->save($slot);
            $slotRepository->flush();
            $this->addFlash('success', 'Buchung erfolgreich');
        } else {
            $this->addFlash('warning', 'Es tut mir leid aber der Slot ist bereits vergeben');
        }

        return $this->redirectToRoute('app_slots');
    }

    #[Route('/', name: 'app_slots')]
    public function index(DistributionRepository $distributionRepository): Response
    {
        $dists = $distributionRepository->findCurrentDistributions();

        return $this->render('slot_booking/index.html.twig', [
            'dists' => $dists
        ]);
    }
}
