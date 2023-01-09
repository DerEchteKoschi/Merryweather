<?php

namespace App\Controller;

use App\Repository\DistributionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlotBookingController extends AbstractController
{
    #[Route('/', name: 'app_slot_booking')]
    public function index(DistributionRepository $distributionRepository): Response
    {
        $dist = $distributionRepository->findCurrentDistribution();
        return $this->render('slot_booking/index.html.twig', [
            'dist' => $dist
        ]);
    }
}
