<?php

namespace App\Controller\Admin;

use App\Entity\Distribution;
use App\Entity\Slot;
use App\Repository\SlotRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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
use Symfony\Component\HttpFoundation\Response;

class DistributionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Distribution::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $createSlotsAction = Action::new('create_slots')
                                   ->addCssClass('btn btn-success')
                                   ->setIcon('fa fa-check-circle')
                                   ->displayIf(static function (Distribution $distribution): bool {
                                       return $distribution->getSlots()->isEmpty();
                                   })
                                   ->displayAsButton()->linkToCrudAction('createSlots')->setTemplatePath('admin/create_slots.html.twig');

        return parent::configureActions($actions)
                     ->add(Crud::PAGE_DETAIL, $createSlotsAction)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)->overrideTemplate('crud/new', 'admin/distnew.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('text'),
            CollectionField::new('slots')->setLabel('Slot Count')->hideOnForm()->formatValue(static function ($value, Distribution $distribution) {
                return $distribution->getSlots()->count();
            }),
            DateField::new('activeFrom'),
            DateField::new('activeTill'),
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
        }

        return parent::createNewForm($entityDto, $formOptions, $context);
    }

    public function createSlots(AdminContext $adminContext, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, SlotRepository $slotRepository): Response
    {
        $distribution = $adminContext->getEntity()->getInstance();
        if (!$distribution instanceof Distribution) {
            throw new \LogicException('Entity is missing or not a Distribution');
        }
        try {
            $startTime = new DateTimeImmutable($adminContext->getRequest()->get('starttime'));
            $targetTime = new DateTimeImmutable($adminContext->getRequest()->get('endtime'));
            $size = (int)$adminContext->getRequest()->get('slotsize');

            while ($startTime < $targetTime) {
                $slot = new Slot();
                $slot->setStartAt($startTime);
                $slot->setText($distribution->getText() . ': Slot ' . $startTime->format('H:i'));
                $startTime = $startTime->add(new DateInterval('PT' . $size . 'M'));
                $slot->setDistribution($distribution);
                $slotRepository->save($slot);
            }

            $entityManager->flush();
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }
        $targetUrl = $adminUrlGenerator
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
}
