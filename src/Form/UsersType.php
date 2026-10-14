<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, ['label' => 'Identifiant'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('password', PasswordType::class, ['label' => 'Mot de passe'])
            ->add('roles', ChoiceType::class, [
                'label' => 'Roles',
                'choices' => ['Utilisateur' => 'ROLE_USER', 'Gestionnaire' => 'ROLE_MANAGER', 'Administrateur' => 'ROLE_ADMIN'],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('fullName', TextType::class, ['label' => 'Nom complet', 'required' => false])
            ->add('phone', TextType::class, ['label' => 'Telephone', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Users::class]);
    }
}
