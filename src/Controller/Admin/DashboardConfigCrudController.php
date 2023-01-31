<?php

namespace App\Controller\Admin;

use App\Entity\AppConfig;
use App\MerryWeather\Admin\AppConfig as AppCfg;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DashboardConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AppConfig::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('configKey')->setChoices(array_flip(AppCfg::CONFIG_KEYS)),
            TextField::new('value'),
        ];
    }
}
