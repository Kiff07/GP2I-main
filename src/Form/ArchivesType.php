<?php

namespace App\Form;

use App\Entity\Archives;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArchivesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('entity', TextType::class, ['label' => 'Entite'])
            ->add('entityId', IntegerType::class, ['label' => 'ID entite'])
            ->add('action', TextType::class, ['label' => 'Action'])
            ->add('data', TextareaType::class, ['label' => 'Donnees JSON', 'required' => false, 'mapped' => false])
            ->add('createdBy', EntityType::class, ['label' => 'Cree par', 'class' => Users::class, 'choice_label' => 'fullName', 'placeholder' => 'Systeme', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Archives::class]);
    }
}
