<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 200)],
                'attr' => ['placeholder' => 'Ex: Perruque Lace Front Bob 14"'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4, 'placeholder' => 'Description courte du produit…'],
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (FCFA)',
                'scale' => 0,
                'constraints' => [new Assert\NotBlank(), new Assert\Positive()],
                'attr' => ['placeholder' => '25000'],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock disponible',
                'constraints' => [new Assert\NotNull(), new Assert\GreaterThanOrEqual(0)],
                'attr' => ['min' => 0],
            ])
            ->add('categorie', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Categorie::class,
                'query_builder' => fn($repo) => $repo->createQueryBuilder('c')
                    ->where('c.type = :type')
                    ->setParameter('type', 'boutique')
                    ->orderBy('c.nom', 'ASC'),
                'choice_label' => 'nom',
                'placeholder' => '— Choisir une catégorie —',
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Photo du produit',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File(
                        maxSize: '3M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Format accepté : JPG, PNG ou WebP (3 Mo max)',
                    ),
                ],
                'attr' => ['accept' => 'image/jpeg,image/png,image/webp'],
                'help' => 'Laissez vide pour conserver la photo actuelle. JPG, PNG ou WebP · 3 Mo max.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Produit::class]);
    }
}
