<?php

namespace App\Controller\Admin;

use App\Cronjobs;
use App\Entity\Crontab;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CrontabCrudController extends AbstractCrudController
{
    public function __construct(private Cronjobs $cronjobs)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Crontab::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('expression'),
            DateTimeField::new('last_execution')->hideOnForm(),
            DateTimeField::new('next_execution')->hideOnForm(),
            ChoiceField::new('command')->setChoices($this->cronjobs->generate()),
            TextField::new('arguments'),
            TextareaField::new('result')->hideOnForm(),
        ];
    }
}
