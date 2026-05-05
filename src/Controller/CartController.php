<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Service\CartService;
use App\Service\EmailService;
use App\Service\PaydunyaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panier', name: 'app_cart')]
final class CartController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(CartService $cart): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cart->getItems(),
            'total' => $cart->getTotal(),
        ]);
    }

    #[Route('/ajouter/{id}', name: '_add', methods: ['POST'])]
    public function add(int $id, Request $request, CartService $cart): Response
    {
        $qty = max(1, (int) $request->request->get('qty', 1));
        $cart->add($id, $qty);

        if ($request->isXmlHttpRequest()) {
            $count = array_sum($request->getSession()->get('cart', []));
            return new JsonResponse(['success' => true, 'cartCount' => $count]);
        }

        $this->addFlash('success', 'Article ajouté au panier.');
        return $this->redirect($request->headers->get('referer', $this->generateUrl('app_boutique')));
    }

    #[Route('/modifier/{id}', name: '_update', methods: ['POST'])]
    public function update(int $id, Request $request, CartService $cart): Response
    {
        $qty = (int) $request->request->get('qty', 1);
        $cart->updateQty($id, $qty);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/supprimer/{id}', name: '_remove', methods: ['POST'])]
    public function remove(int $id, CartService $cart): Response
    {
        $cart->remove($id);
        $this->addFlash('success', 'Article retiré du panier.');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/commander', name: '_order', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function order(Request $request, CartService $cart, EntityManagerInterface $em, PaydunyaService $paydunya, EmailService $emailService): Response
    {
        $items = $cart->getItems();

        if (empty($items)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        if ($request->isMethod('POST')) {
            // 1. Créer la commande
            $commande = new Commande();
            $commande->setClient($this->getUser())
                     ->setAdresseLivraison($request->request->get('adresse'))
                     ->setTelephone($request->request->get('telephone'))
                     ->setStatut(Commande::STATUT_EN_ATTENTE);

            foreach ($items as $item) {
                $ligne = new LigneCommande();
                $ligne->setProduit($item['produit'])
                      ->setQuantite($item['quantite'])
                      ->setPrixUnitaire($item['produit']->getPrix());
                $commande->addLigne($ligne);
                $em->persist($ligne);
            }

            $commande->calculerTotal();
            $em->persist($commande);
            $em->flush();
            $cart->clear();
            try { $emailService->sendCommandeConfirmation($commande); } catch (\Throwable) {}

            // 2. Si PayDunya est configuré → redirection vers le paiement en ligne
            if ($paydunya->isConfigured()) {
                $result = $paydunya->createInvoice(
                    $commande,
                    $this->generateUrl('app_paiement_retour', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    $this->generateUrl('app_paiement_annule', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    $this->generateUrl('app_paiement_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL),
                );

                if (isset($result['invoice_url'])) {
                    $commande->setReferencePaiement($result['token']);
                    $em->flush();
                    return $this->redirect($result['invoice_url']);
                }

                $erreur = $result['error'] ?? 'Erreur inconnue';
                $this->addFlash('danger', 'PayDunya : ' . $erreur);
            }

            // 3. Pas encore configuré ou erreur → page de paiement avec simulation
            return $this->render('cart/paiement.html.twig', ['commande' => $commande]);
        }

        return $this->render('cart/order.html.twig', [
            'items' => $items,
            'total' => $cart->getTotal(),
        ]);
    }

    #[Route('/confirmation/{id}', name: '_confirmation', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function confirmation(Commande $commande): Response
    {
        if ($commande->getClient() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('cart/confirmation.html.twig', [
            'commande' => $commande,
        ]);
    }
}
