<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = null !== $options['data']?->getId();

        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez entrer le prénom.'),
                    new Assert\Length(min: 2, minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.'),
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez entrer le nom.'),
                    new Assert\Length(min: 2, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez entrer un email.'),
                    new Assert\Email(message: 'Veuillez entrer un email valide.'),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Usager' => 'ROLE_USER',
                    'Bibliothécaire' => 'ROLE_LIBRARIAN',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'mapped' => false,
                'multiple' => false,
                'expanded' => false,
                'data' => $isEdit && isset($options['data']) && $options['data']->getRoles() ? $options['data']->getRoles()[0] : 'ROLE_USER',
            ])
        ;

        if (!$isEdit) {
            $builder->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => ['placeholder' => '••••••••'],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['placeholder' => '••••••••'],
                ],
                'invalid_message' => 'Les deux mots de passe doivent être identiques.',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez entrer un mot de passe.'),
                    new Assert\Length(
                        min: 8,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        max: 4096,
                    ),
                    new Assert\Regex(
                        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                        message: 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.'
                    ),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
