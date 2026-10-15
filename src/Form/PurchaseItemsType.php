<?php

namespace App\Form;

use App\Entity\PurchaseItems;
use App\Entity\PurchaseOrders;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseItemsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('purchaseOrder', EntityType::class, ['label' => 'Commande', 'class' => PurchaseOrders::class])
            ->add('materialName', TextType::class, ['label' => 'Matériel'])
            ->add('materialReference', TextType::class, ['label' => 'Référence', 'required' => false])
            ->add('quantity', IntegerType::class, ['label' => 'Quantité'])
            ->add('unitPrice', MoneyType::class, ['label' => 'Prix unitaire', 'currency' => 'EUR', 'required' => false])
            ->add('receivedQuantity', IntegerType::class, ['label' => 'Quantité reçue'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PurchaseItems::class]);
    }
}
