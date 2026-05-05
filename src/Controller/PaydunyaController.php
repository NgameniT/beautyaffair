<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Service\PaydunyaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/paiement')]
final class PaydunyaController extends AbstractController
{
    // ── Retour après paiement (PayDunya redirige l'utilisateur ici) ────────────
    #[Route('/retour/{id}', name: 'app_paiement_retour')]
    #[IsGranted('ROLE_USER')]
    public function retour(
        Commande $commande,
        Request $request,
        PaydunyaService $paydunya,
        EntityManagerInterface $em,
    ): Response {
        if ($commande->getClient() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Déjà payée (double visite de la page)
        if ($commande->getStatut() === Commande::STATUT_PAYEE) {
            return $this->render('cart/confirmation.html.twig', ['commande' => $commande]);
        }

        $token = $request->query->get('token') ?? $commande->getReferencePaiement();

        if ($token && $paydunya->isConfigured()) {
            $statut = $paydunya->verifyPayment($token);
            if ($statut === 'completed') {
                $commande->setStatut(Commande::STATUT_PAYEE);
                $commande->setReferencePaiement($token);
                $em->flush();
                return $this->render('cart/confirmation.html.twig', ['commande' => $commande]);
            }
        }

        return $this->render('cart/paiement_echec.html.twig', ['commande' => $commande]);
    }

    // ── Annulation par l'utilisateur ────────────────────────────────────────────
    #[Route('/annule/{id}', name: 'app_paiement_annule')]
    #[IsGranted('ROLE_USER')]
    public function annule(Commande $commande, EntityManagerInterface $em): Response
    {
        if ($commande->getClient() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($commande->getStatut() === Commande::STATUT_EN_ATTENTE) {
            $commande->setStatut(Commande::STATUT_ANNULEE);
            $em->flush();
        }

        $this->addFlash('warning', 'Paiement annulé. Votre commande a été annulée.');
        return $this->redirectToRoute('app_boutique');
    }

    // ── Webhook PayDunya (notification serveur → serveur) ──────────────────────
    #[Route('/webhook', name: 'app_paiement_webhook', methods: ['POST'])]
    public function webhook(
        Request $request,
        CommandeRepository $repo,
        PaydunyaService $paydunya,
        EntityManagerInterface $em,
    ): Response {
        $data = json_decode($request->getContent(), true);

        $token      = $data['data']['invoice']['token']       ?? null;
        $statut     = $data['data']['invoice']['status']      ?? null;
        $commandeId = $data['data']['custom_data']['commande_id'] ?? null;

        if (!$token || !$commandeId) {
            return new Response('Bad Request', 400);
        }

        // Vérification directe auprès de PayDunya (sécurité)
        $statutVerifie = $paydunya->verifyPayment($token);

        if ($statutVerifie === 'completed') {
            $commande = $repo->find($commandeId);
            if ($commande && $commande->getStatut() === Commande::STATUT_EN_ATTENTE) {
                $commande->setStatut(Commande::STATUT_PAYEE);
                $commande->setReferencePaiement($token);
                $em->flush();
            }
        }

        return new Response('OK', 200);
    }

    // ── Simulation paiement (dev uniquement, mode test) ─────────────────────────
    #[Route('/simuler/{id}', name: 'app_paiement_simuler', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function simuler(Commande $commande, EntityManagerInterface $em): Response
    {
        if ($commande->getClient() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $commande->setStatut(Commande::STATUT_PAYEE);
        $commande->setReferencePaiement('SIMULE-' . uniqid());
        $em->flush();

        $this->addFlash('success', 'Paiement simulé avec succès. Commande #' . $commande->getId() . ' payée.');
        return $this->render('cart/confirmation.html.twig', ['commande' => $commande]);
    }
}
