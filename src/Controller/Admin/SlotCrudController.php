<?php

namespace App\Controller\Admin;

use App\Entity\Distribution;
use App\Entity\Slot;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class SlotCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Slot::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            'text',
            'startAt',
            FormField::addPanel('Details')->collapsible()
                     ->setIcon('fa fa-info')
                     ->setHelp('Additional Details'),
            AssociationField::new('distribution')->setCrudController(DistributionCrudController::class),
            AssociationField::new('user')->setCrudController(UserCrudController::class),
        ];
    }

}
