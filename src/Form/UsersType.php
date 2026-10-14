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

/**
 * AVANT : le champ "password" etait mappe directement sur l'entite.
 * En edition, le formulaire ecrasait donc le mot de passe hashe stocke
 * en base par le texte tape (ou par une chaine vide hachee !).
 *
 * APRES : "plainPassword" est non-mappe (mapped: false). Le controleur
 * le hashe uniquement s'il est rempli. En edition, le laisser vide
 * conserve le mot de passe actuel.
 *
 * Option "password_required" : true a la creation, false a l'edition.
 */
class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, ['label' => 'Identifiant'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('plainPassword', PasswordType::class, [
                'label' => $options['password_required']
                    ? 'Mot de passe'
                    : 'Nouveau mot de passe (laisser vide pour conserver l\'actuel)',
                'mapped' => false,
                'required' => (bool) $options['password_required'],
                'attr' => ['autocomplete' => 'new-password', 'minlength' => 8],
                'help' => '8 caracteres minimum.',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Roles',
                'choices' => ['Utilisateur (lecture)' => 'ROLE_USER', 'Gestionnaire (stock)' => 'ROLE_MANAGER', 'Administrateur' => 'ROLE_ADMIN'],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('fullName', TextType::class, ['label' => 'Nom complet', 'required' => false])
            ->add('phone', TextType::class, ['label' => 'Telephone', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
            'password_required' => true,
        ]);
        $resolver->setAllowedTypes('password_required', 'bool');
    }
}
