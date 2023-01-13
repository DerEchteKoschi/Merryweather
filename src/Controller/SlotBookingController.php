<?php

namespace App\Controller;

use App\Repository\DistributionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlotBookingController extends AbstractController
{
    #[Route('/', name: 'app_slots')]
    public function index(DistributionRepository $distributionRepository): Response
    {
        $dist = $distributionRepository->findCurrentDistribution();
        return $this->render('slot_booking/index.html.twig', [
            'dist' => $dist
        ]);
    }

    #[Route('/book', name: 'app_slot_book')]
    public function book(): Response {
        $this->addFlash('success', 'jippie');
        return $this->redirectToRoute('app_slots');
    }
}
