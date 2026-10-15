<?php

namespace App\Form;

use App\Entity\InventoryMovements;
use App\Entity\Materials;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryMovementsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('material', EntityType::class, ['label' => 'Matériel', 'class' => Materials::class, 'choice_label' => 'name'])
            ->add('movementType', ChoiceType::class, ['label' => 'Type', 'choices' => InventoryMovements::typeChoices()])
            ->add('quantity', IntegerType::class, ['label' => 'Quantité'])
            ->add('fromLocation', TextType::class, ['label' => 'Emplacement source', 'required' => false])
            ->add('toLocation', TextType::class, ['label' => 'Emplacement destination', 'required' => false])
            ->add('performedBy', EntityType::class, ['label' => 'Effectué par', 'class' => Users::class, 'choice_label' => 'fullName', 'placeholder' => 'Non renseigné', 'required' => false])
            ->add('performedAt', DateType::class, ['label' => 'Date', 'widget' => 'single_text'])
            ->add('reason', TextareaType::class, ['label' => 'Motif', 'required' => false, 'attr' => ['rows' => 4]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => InventoryMovements::class]);
    }
}
