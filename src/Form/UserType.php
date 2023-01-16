<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\Common\Annotations\Annotation\Enum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('display_name')
            ->add('roles', CollectionType::class, ['allow_add' => true])
            ->add('firstname')
            ->add('lastname')
            ->add('email')
            ->add('phone')
            ->add('active')
            ->add('score')

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
