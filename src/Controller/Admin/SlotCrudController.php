<?php

namespace App\Controller\Admin;

use App\Entity\Slot;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

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
            AssociationField::new('distribution')->setCrudController(DistributionCrudController::class),
            AssociationField::new('user')->setCrudController(UserCrudController::class),
        ];
    }
}
