<?php

namespace App\Controller;

use App\Dto\Distribution;
use App\Dto\Slot;
use App\Entity\User;
use App\Events\SlotBookedEvent;
use App\Events\SlotCanceledEvent;
use App\Merryweather\BookingRuleChecker;
use App\Repository\DistributionRepository;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Psr\EventDispatcher\EventDispatcherInterface;
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

    public function __construct(private readonly TranslatorInterface $translator, private readonly EventDispatcherInterface $eventDispatcher)
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
                    $pointsPayed = $bookRuleChecker->lowerUserScoreBySlot($user, $slot);
                    $slot->setAmountPaid($pointsPayed);
                    $slotRepository->save($slot, true);
                    $userRepository->save($user, true);
                    $this->logger->info(sprintf('User %s booked Slot %s', $user, $slot->getText()));
                    $this->eventDispatcher->dispatch(new SlotBookedEvent($slot), SlotBookedEvent::NAME);
                    $this->addFlash('success', $this->translator->trans('booking_successful'));
                } else {
                    $this->logger->warning(sprintf('User %s tried to book Slot %s', $user, $slot->getText()));
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
            $this->logger->info(sprintf('User %s canceled Slot %s', $user, $slot->getText()));
            $this->eventDispatcher->dispatch(new SlotCanceledEvent($slot), SlotCanceledEvent::NAME);
            $this->addFlash('success', 'Stornierung erfolgreich');
        } elseif ($slot->getUser() !== null) {
            $this->logger->alert(sprintf('User %s tried to cancel Slot %s that belongs to %s', $user, $slot->getText(), $slot->getUser()));
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

    #[Route('/slot/{slotId}', name: 'app_slot_row')]
    public function slotRow(int $slotId, SlotRepository $slotRepository): Response
    {
        $slot = $slotRepository->find($slotId);
        return $this->render('slot_booking/listitem.html.twig', [
            'slot' => Slot::fromEntity($slot)
        ]);
    }


}
