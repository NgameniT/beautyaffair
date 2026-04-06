<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du livre',
                'attr' => ['placeholder' => 'Ex: Le Petit Prince'],
                'constraints' => [new NotBlank(message: 'Le titre est requis.')],
            ])
            ->add('author', TextType::class, [
                'label' => 'Auteur',
                'attr' => ['placeholder' => 'Ex: Antoine de Saint-Exupéry'],
                'constraints' => [new NotBlank(message: 'L\'auteur est requis.')],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Littérature' => 'Litterature',
                    'Dystopie' => 'Dystopie',
                    'Classique' => 'Classique',
                    'Fantastique' => 'Fantastique',
                    'Philosophie' => 'Philosophie',
                    'Science-Fiction' => 'Science-Fiction',
                    'Aventure' => 'Aventure',
                    'Romance' => 'Romance',
                    'Policier' => 'Policier',
                    'Biographie' => 'Biographie',
                ],
                'constraints' => [new NotBlank(message: 'Sélectionnez une catégorie.')],
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Langue',
                'choices' => [
                    'Français' => 'Francais',
                    'Anglais' => 'Anglais',
                    'Espagnol' => 'Espagnol',
                    'Allemand' => 'Allemand',
                    'Italien' => 'Italien',
                    'Portugais' => 'Portugais',
                    'Russe' => 'Russe',
                    'Japonais' => 'Japonais',
                    'Chinois' => 'Chinois',
                ],
                'constraints' => [new NotBlank(message: 'Sélectionnez une langue.')],
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Description / Résumé',
                'attr' => ['placeholder' => 'Décrivez le contenu du livre en quelques lignes...', 'rows' => 5],
                'constraints' => [
                    new NotBlank(message: 'Une description est requise.'),
                    new Length(min: 20, minMessage: 'La description doit contenir au moins {{ limit }} caractères.'),
                ],
            ])
            ->add('publishedYear', IntegerType::class, [
                'label' => 'Année de publication',
                'attr' => ['placeholder' => 'Ex: 1943', 'type' => 'number'],
            ])
            ->add('pageCount', IntegerType::class, [
                'label' => 'Nombre de pages',
                'attr' => ['placeholder' => 'Ex: 96', 'type' => 'number'],
                'constraints' => [new PositiveOrZero(message: 'Le nombre de pages doit être positif.')],
            ])
            ->add('availableCopies', IntegerType::class, [
                'label' => 'Stock disponible',
                'attr' => ['placeholder' => 'Ex: 5', 'type' => 'number'],
                'constraints' => [new PositiveOrZero(message: 'Le stock doit être positif ou zéro.')],
            ])
            ->add('coverTheme', ChoiceType::class, [
                'label' => 'Thème visuel (couleur de couverture)',
                'choices' => [
                    'Jaune' => 'yellow',
                    'Sombre' => 'dark',
                    'Rouge' => 'red',
                    'Violet' => 'purple',
                    'Bleu' => 'blue',
                    'Vert' => 'green',
                    'Cyan' => 'teal',
                    'Orange' => 'orange',
                ],
                'help' => 'Choisissez la couleur de la couverture du livre',
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug (identifiant URL)',
                'attr' => ['placeholder' => 'Ex: le-petit-prince'],
                'help' => 'Caractères autorisés: lettres, chiffres, tirets. Pas d\'espaces.',
                'constraints' => [new NotBlank(message: 'Le slug est requis.')],
            ])
            ->add('imageUrl', TextType::class, [
                'label' => 'URL de l\'image de couverture',
                'attr' => ['placeholder' => 'https://example.com/image.jpg'],
                'required' => false,
                'help' => 'Optionnel. Lien complet vers l\'image.',
            ])
            ->add('featured', CheckboxType::class, [
                'label' => 'Mettre en avant (afficher en avant-plan)',
                'required' => false,
                'help' => 'Cochez pour afficher ce livre sur la page d\'accueil.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
