<?php

namespace App\Form;

use App\Entity\PurchaseOrders;
use App\Entity\Suppliers;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseOrdersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('supplier', EntityType::class, ['label' => 'Fournisseur', 'class' => Suppliers::class, 'choice_label' => 'name', 'placeholder' => 'Aucun', 'required' => false])
            ->add('orderNumber', TextType::class, ['label' => 'Numéro de commande'])
            ->add('orderedBy', EntityType::class, ['label' => 'Commande par', 'class' => Users::class, 'choice_label' => 'fullName', 'placeholder' => 'Non renseigné', 'required' => false])
            ->add('status', ChoiceType::class, ['label' => 'Statut', 'choices' => PurchaseOrders::statusChoices()])
            ->add('orderDate', DateType::class, ['label' => 'Date de commande', 'widget' => 'single_text'])
            ->add('expectedDate', DateType::class, ['label' => 'Date attendue', 'widget' => 'single_text', 'required' => false])
            ->add('totalAmount', MoneyType::class, ['label' => 'Montant total', 'currency' => 'EUR', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PurchaseOrders::class]);
    }
}
