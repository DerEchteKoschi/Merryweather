<?php

namespace App\Controller;

use App\Dto\Distribution;
use App\Dto\Slot;
use App\Merryweather\BookingException;
use App\Merryweather\BookingService;
use App\Repository\DistributionRepository;
use App\Repository\SlotRepository;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}')]
class SlotBookingController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly TranslatorInterface $translator, private readonly BookingService $bookingService)
    {
    }

    #[Route('/book/{slotId}', name: 'app_slot_book')]
    public function book(int $slotId): Response
    {
        try {
            $this->bookingService->bookSlot($slotId);
            $this->addFlash('success', $this->translator->trans('booking_successful'));
        } catch (BookingException $bfe) {
            $this->addFlash($bfe->getCode() === BookingException::CRITICAL ? 'danger' : 'warning', $this->translator->trans($bfe->getMessage()));
        }
        return $this->redirectToRoute('app_slots');
    }


    #[Route('/cancel/{slotId}', name: 'app_slot_cancel')]
    public function cancel(int $slotId): Response
    {
        try {
            $this->bookingService->cancelSlot($slotId);
            $this->addFlash('success', $this->translator->trans('cancel_successful'));
        } catch (BookingException $bfe) {
            $this->addFlash($bfe->getCode() === BookingException::CRITICAL ? 'danger' : 'warning', $this->translator->trans($bfe->getMessage()));
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

    #[Route('/slot/{slotId}', name: 'app_slot_row')]
    public function slotRow(int $slotId, SlotRepository $slotRepository): Response
    {
        $slot = $slotRepository->find($slotId);
        return $this->render('slot_booking/listitem.html.twig', [
            'slot' => Slot::fromEntity($slot)
        ]);
    }

    #[Route('/slotsList', name: 'app_slot_list')]
    public function slotList(DistributionRepository $distributionRepository): Response
    {
        $dists = Distribution::fromList($distributionRepository->findCurrentDistributions());

        return $this->render('slot_booking/list.html.twig', [
            'dists' => $dists
        ]);
    }
}
