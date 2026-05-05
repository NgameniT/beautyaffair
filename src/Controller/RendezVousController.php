<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\RendezVousRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rendez-vous', name: 'app_rdv')]
final class RendezVousController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(RendezVousRepository $repo): Response
    {
        if (!$this->getUser()) {
            return $this->render('rendez_vous/accueil.html.twig');
        }

        $rdvs = $repo->findBy(
            ['client' => $this->getUser()],
            ['dateHeure' => 'DESC']
        );

        return $this->render('rendez_vous/index.html.twig', ['rdvs' => $rdvs]);
    }

    #[Route('/reserver', name: '_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em, EmailService $email): Response
    {
        $rdv  = new RendezVous();
        $form = $this->createForm(RendezVousType::class, $rdv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date  = $form->get('date')->getData();
            $heure = $form->get('heure')->getData();

            $dateHeure = new \DateTime($date->format('Y-m-d') . ' ' . $heure->format('H:i'));
            $rdv->setDateHeure($dateHeure);
            $rdv->setClient($this->getUser());

            $em->persist($rdv);
            $em->flush();

            try { $email->sendRdvConfirmation($rdv); } catch (\Throwable) {}

            $this->addFlash('success', 'Votre rendez-vous a été enregistré ! Vous recevrez une confirmation par email sous 24h.');
            return $this->redirectToRoute('app_rdv');
        }

        return $this->render('rendez_vous/new.html.twig', ['form' => $form]);
    }

    #[Route('/annuler/{id}', name: '_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(RendezVous $rdv, EntityManagerInterface $em): Response
    {
        if ($rdv->getClient() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($rdv->getStatut() === RendezVous::STATUT_EN_ATTENTE || $rdv->getStatut() === RendezVous::STATUT_CONFIRME) {
            $rdv->setStatut(RendezVous::STATUT_ANNULE);
            $em->flush();
            $this->addFlash('success', 'Votre rendez-vous a été annulé.');
        }

        return $this->redirectToRoute('app_rdv');
    }
}
