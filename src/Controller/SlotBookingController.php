<?php

namespace App\Controller;

use App\Repository\DistributionRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlotBookingController extends AbstractController
{
    #[Route('/', name: 'app_slot_booking')]
    public function index(DistributionRepository $distributionRepository): Response
    {
        //return new Response(print_r($distributionRepository->findCurrentDistribution()->getText(), true));
        return $this->render('slot_booking/index.html.twig', [
            'controller_name' => 'SlotBookingController',
        ]);
    }
}
