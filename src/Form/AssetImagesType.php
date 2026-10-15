<?php

namespace App\Form;

use App\Entity\AssetImages;
use App\Entity\Materials;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AssetImagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('material', EntityType::class, ['label' => 'Matériel', 'class' => Materials::class, 'choice_label' => 'name'])
            // Vrai envoi de fichier (avant : un simple nom tape au clavier).
            ->add('imageFile', FileType::class, [
                'label' => 'Photo (JPG, PNG ou WebP, 2 Mo max)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Merci d\'envoyer une image JPG, PNG ou WebP.',
                    ),
                ],
            ])
            ->add('caption', TextType::class, ['label' => 'Légende', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AssetImages::class]);
    }
}
