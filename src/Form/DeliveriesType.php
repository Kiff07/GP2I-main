<?php

namespace App\Form;

use App\Entity\Deliveries;
use App\Entity\PurchaseOrders;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('purchaseOrder', EntityType::class, ['label' => 'Commande', 'class' => PurchaseOrders::class, 'placeholder' => 'Aucune', 'required' => false])
            ->add('deliveryDate', DateType::class, ['label' => 'Date de livraison', 'widget' => 'single_text'])
            ->add('receivedBy', EntityType::class, ['label' => 'Reçu par', 'class' => Users::class, 'choice_label' => 'fullName', 'placeholder' => 'Non renseigné', 'required' => false])
            ->add('note', TextareaType::class, ['label' => 'Note', 'required' => false, 'attr' => ['rows' => 4]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Deliveries::class]);
    }
}
