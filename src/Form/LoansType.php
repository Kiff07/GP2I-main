<?php

namespace App\Form;

use App\Entity\Loans;
use App\Entity\Materials;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoansType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('material', EntityType::class, ['label' => 'Matériel', 'class' => Materials::class, 'choice_label' => 'name'])
            ->add('borrowerName', TextType::class, ['label' => 'Emprunteur (nom de la personne)'])
            ->add('borrowedAt', DateType::class, ['label' => 'Date d\'emprunt', 'widget' => 'single_text'])
            ->add('dueDate', DateType::class, ['label' => 'Retour prévu le', 'widget' => 'single_text'])
            ->add('note', TextareaType::class, ['label' => 'Note', 'required' => false, 'attr' => ['rows' => 3]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Loans::class]);
    }
}
