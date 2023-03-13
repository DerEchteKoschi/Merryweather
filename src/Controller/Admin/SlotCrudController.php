<?php

namespace App\Controller\Admin;

use App\Entity\Slot;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class SlotCrudController extends AbstractCrudController
{
    /**
     * @codeCoverageIgnore
     */
    public static function getEntityFqcn(): string
    {
        return Slot::class;
    }


    /**
     * @codeCoverageIgnore
     */
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
