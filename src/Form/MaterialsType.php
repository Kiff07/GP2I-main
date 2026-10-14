<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Materials;
use App\Entity\Suppliers;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaterialsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('reference', TextType::class, [
                'label' => 'Reference',
            ])
            ->add('serialNumber', TextType::class, [
                'label' => 'Numero de serie',
                'required' => false,
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantite',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => Materials::statusChoices(),
            ])
            ->add('location', TextType::class, [
                'label' => 'Emplacement',
                'required' => false,
            ])
            ->add('purchaseDate', DateType::class, [
                'label' => 'Date d achat',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('purchasePrice', MoneyType::class, [
                'label' => 'Prix d achat',
                'required' => false,
                'currency' => 'EUR',
            ])
            ->add('category', EntityType::class, [
                'label' => 'Categorie',
                'class' => Categories::class,
                'choice_label' => 'name',
                'placeholder' => 'Aucune categorie',
                'required' => false,
            ])
            // CORRECTION : le champ fournisseur manquait. Impossible donc
            // d'affecter un fournisseur a un materiel depuis l'interface,
            // alors que la liste propose un filtre par fournisseur.
            ->add('supplier', EntityType::class, [
                'label' => 'Fournisseur',
                'class' => Suppliers::class,
                'choice_label' => 'name',
                'placeholder' => 'Aucun fournisseur',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Materials::class,
        ]);
    }
}
