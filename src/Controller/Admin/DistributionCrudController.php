<?php

namespace App\Controller\Admin;

use App\Entity\Distribution;
use App\Entity\Slot;
use App\Entity\User;
use App\MerryWeather\Admin\AppConfig;
use App\MerryWeather\BookingRuleChecker;
use App\Repository\DistributionRepository;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DistributionCrudController extends AbstractCrudController
{
    public function __construct(private AppConfig $config)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Distribution::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $controller = $this;

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('text'),
            CollectionField::new('slots')->setLabel('Anzahl Slots')->hideOnForm()->formatValue(static function ($value, Distribution $distribution) use ($controller) {
                if ($distribution->getSlots()->count() === 0) {
                    return $controller->renderView('admin/create_slots.html.twig', ['linkUrl' => $controller->generateUrl('app_admin_slots_create', ['distributionId' => $distribution->getId()])]);
                }

                return $distribution->getSlots()->count();
            }),
            DateField::new('activeFrom'),
            DateField::new('activeTill'),
            CollectionField::new('slots')
                           ->setLabel('gebuchte Slots')
                           ->hideOnIndex()
                           ->hideOnForm()
                           ->formatValue(static function ($value, Distribution $distribution) use ($controller) {
                               $result = '';
                               $template = $controller->config->isAdminCancelAllowed() ? 'admin/slotCancel.html.twig' : 'admin/slot.html.twig';
                               foreach ($distribution->getSlots()->getIterator() as $slot) {
                                   if ($slot->getUser() !== null) {
                                       $result .= $controller->renderView($template, [
                                           'slot' => \App\Dto\Slot::fromEntity($slot),
                                           'cancelUrl' => $controller->generateUrl('app_admin_slot_cancel', [
                                               'slotId' => $slot->getId(),
                                               'distributionId' => $distribution->getId()
                                           ])
                                       ]);
                                   }
                               }

                               return empty($result) ? 'keine Buchungen' : '<div class="container">' . $result . '</div>';
                           })
        ];
    }

    public function createNewForm(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormInterface
    {
        /**
         * @var Distribution $dist
         */
        $dist = $entityDto->getInstance();
        if ($context->getRequest()->get('active_till') !== null) {
            $till = new \DateTimeImmutable($context->getRequest()->get('active_till'));
            $dist->setActiveTill($till);
            $from = $till->sub(new \DateInterval('P6D'));
            $today = new \DateTimeImmutable();
            if ($from < $today) {
                $from = $today;
            }
            $dist->setActiveFrom($from);
            $dist->setText($till->format('d.m.Y'));
        }

        return parent::createNewForm($entityDto, $formOptions, $context);
    }

    #[Route('/admin/createslots/{distributionId}', name: 'app_admin_slots_create')]
    public function createSlots(
        int $distributionId,
        Request $request,
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator,
        SlotRepository $slotRepository,
        DistributionRepository $distributionRepository
    ): Response {
        $distribution = $distributionRepository->find($distributionId);
        if (!$distribution instanceof Distribution) {
            throw new \LogicException('Entity is missing or not a Distribution');
        }
        try {
            $startTime = new DateTimeImmutable($request->get('starttime'));
            $targetTime = new DateTimeImmutable($request->get('endtime'));
            $size = (int)$request->get('slotsize');
            $count = 0;
            while ($startTime < $targetTime) {
                $slot = new Slot();
                $slot->setStartAt($startTime);
                $slot->setText($distribution->getText() . ': Slot ' . $startTime->format('H:i'));
                $startTime = $startTime->add(new DateInterval('PT' . $size . 'M'));
                $slot->setDistribution($distribution);
                $slotRepository->save($slot);
                $count++;
            }
            $this->addFlash('success', sprintf('%d Slots erstellt', $count));
            $entityManager->flush();
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }
        $targetUrl = $adminUrlGenerator
            ->setDashboard(AdminDashboardController::class)
            ->setController(self::class)
            ->setAction(Crud::PAGE_DETAIL)
            ->setEntityId($distribution->getId())
            ->generateUrl();

        return $this->redirect($targetUrl);
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $submitButtonName = $context->getRequest()->request->all()['ea']['newForm']['btn'];

        if (Action::SAVE_AND_RETURN === $submitButtonName) {
            $url = $context->getReferrer()
                   ?? $this->container->get(AdminUrlGenerator::class)->setAction(Action::DETAIL)->setEntityId($context->getEntity()->getPrimaryKeyValue())->generateUrl();

            return $this->redirect($url);
        }

        return parent::getRedirectResponseAfterSave($context, $action);
    }

    #[Route('/admin/cancel/{slotId}/{distributionId}', name: 'app_admin_slot_cancel')]
    public function adminCancel(
        int $slotId,
        int $distributionId,
        SlotRepository $slotRepository,
        UserRepository $userRepository,
        BookingRuleChecker $bookRuleChecker,
        AdminUrlGenerator $adminUrlGenerator
    ): Response {
        $slot = $slotRepository->find($slotId);
        if ($slot === null) {
            $this->addFlash('danger', 'Slot nicht gefunden');
        } else {
            /** @var User $user */
            $user = $slot->getUser();
            $slot->setUser(null);
            $bookRuleChecker->raiseUserScore($user, $bookRuleChecker->pointsNeededForSlot($slot));
            $userRepository->save($user, true);
            $slotRepository->save($slot, true);
            $this->addFlash('success', sprintf('Stornierung erfolgreich %s wurden die benutzten Punkte wieder gutgeschrieben', $user->getDisplayName()));
        }

        return $this->redirect($adminUrlGenerator->setDashboard(AdminDashboardController::class)->setController(DistributionCrudController::class)->setEntityId($distributionId)->setAction('detail')->generateUrl());
    }
}
