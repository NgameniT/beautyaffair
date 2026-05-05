<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $formInfos = $this->createFormBuilder($user, ['attr' => ['id' => 'form-infos']])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr'  => ['placeholder' => 'Votre prénom'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 2),
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr'  => ['placeholder' => 'Votre nom'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 2),
                ],
            ])
            ->getForm();

        $formInfos->handleRequest($request);

        if ($formInfos->isSubmitted() && $formInfos->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Informations mises à jour.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'user'      => $user,
            'formInfos' => $formInfos,
        ]);
    }

    #[Route('/mot-de-passe', name: 'app_profile_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $actuel   = $request->request->get('actuel', '');
        $nouveau  = $request->request->get('nouveau', '');
        $confirme = $request->request->get('confirme', '');

        if (!$hasher->isPasswordValid($user, $actuel)) {
            $this->addFlash('danger', 'Le mot de passe actuel est incorrect.');
            return $this->redirectToRoute('app_profile', ['tab' => 'securite']);
        }

        if (strlen($nouveau) < 8) {
            $this->addFlash('danger', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
            return $this->redirectToRoute('app_profile', ['tab' => 'securite']);
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $nouveau)) {
            $this->addFlash('danger', 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.');
            return $this->redirectToRoute('app_profile', ['tab' => 'securite']);
        }

        if ($nouveau !== $confirme) {
            $this->addFlash('danger', 'Les deux nouveaux mots de passe ne correspondent pas.');
            return $this->redirectToRoute('app_profile', ['tab' => 'securite']);
        }

        $user->setPassword($hasher->hashPassword($user, $nouveau));
        $em->flush();

        $this->addFlash('success', 'Mot de passe modifié avec succès.');
        return $this->redirectToRoute('app_profile');
    }
}
