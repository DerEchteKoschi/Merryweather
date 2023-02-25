<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher, private readonly TranslatorInterface $translator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /**
     * @throws \Exception
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            BooleanField::new('active'),
            Field::new('display_name')->setColumns(5),
            IntegerField::new('score')->formatValue(static function ($value, User $user) {
                return '<span class="badge badge-primary">' . $user->getScore() . '</span>';
            })->hideOnForm(),
            IntegerField::new('score')->setColumns(5)->onlyOnForms(),
            Field::new('firstname')->setColumns(5),
            Field::new('lastname')->setColumns(5),
            EmailField::new('email')->setColumns(5),
            TelephoneField::new('phone')->setColumns(5),
            Field::new('password')->setColumns(5)
                 ->setFormType(RepeatedType::class)
                 ->setFormTypeOptions([
                     'type' => PasswordType::class,
                     'first_options' => ['label' => $this->translator->trans('password'), 'row_attr' => ['class' => 'col-md-5']],
                     'second_options' => ['label' => $this->translator->trans('password_repeat'), 'row_attr' => ['class' => 'col-md-5']],
                     'mapped' => false,
                 ])
                 ->setRequired($pageName === Crud::PAGE_NEW)
                 ->onlyOnForms(),
            ChoiceField::new('roles')
                       ->setChoices([$this->translator->trans('user') => 'ROLE_USER', $this->translator->trans('admin') => 'ROLE_ADMIN'])
                       ->allowMultipleChoices()
                       ->renderExpanded()->renderAsBadges(),
            DateField::new('lastLogin')->hideOnForm(),
            DateField::new('lastVisit')->hideOnForm(),
        ];
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }

    public function createEntity(string $entityFqcn): User
    {
        $user = parent::createEntity($entityFqcn);
        $user->setActive(true);

        return $user;
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    private function hashPassword(): \Closure
    {
        return function ($event) {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }
            $password = $form->get('password')->getData();
            if ($password === null) {
                return;
            }

            /** @var PasswordAuthenticatedUserInterface $user */
            $user = $this->getUser();
            $hash = $this->userPasswordHasher->hashPassword($user, $password);
            $form->getData()->setPassword($hash);
        };
    }
}
