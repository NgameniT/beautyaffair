<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\RendezVous;
use App\Entity\User;
use App\Form\ProduitType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\RendezVousRepository;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    // ─── DASHBOARD ───────────────────────────────────────────────────────────

    #[Route('', name: 'app_admin_dashboard')]
    public function dashboard(
        UserRepository      $users,
        RendezVousRepository $rdvs,
        CommandeRepository  $commandes,
        ProduitRepository   $produits,
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                'clients'          => $users->count(['roles' => []]),
                'rdv_en_attente'   => $rdvs->count(['statut' => RendezVous::STATUT_EN_ATTENTE]),
                'rdv_confirmes'    => $rdvs->count(['statut' => RendezVous::STATUT_CONFIRME]),
                'commandes'        => $commandes->count(['statut' => Commande::STATUT_EN_ATTENTE]),
                'produits'         => $produits->count(['actif' => true]),
                'total_clients'    => $users->count([]),
            ],
            'rdvs_recent'  => $rdvs->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }

    // ─── RENDEZ-VOUS ─────────────────────────────────────────────────────────

    #[Route('/rendez-vous', name: 'app_admin_rdv_list')]
    public function rdvList(RendezVousRepository $repo): Response
    {
        return $this->render('admin/rdv/index.html.twig', [
            'rdvs' => $repo->findBy([], ['dateHeure' => 'ASC']),
        ]);
    }

    #[Route('/rendez-vous/{id}/statut/{statut}', name: 'app_admin_rdv_statut', methods: ['POST'])]
    public function rdvStatut(RendezVous $rdv, string $statut, EntityManagerInterface $em, EmailService $email): Response
    {
        $allowed = [RendezVous::STATUT_CONFIRME, RendezVous::STATUT_ANNULE, RendezVous::STATUT_TERMINE];
        if (in_array($statut, $allowed)) {
            $rdv->setStatut($statut);
            $em->flush();
            try { $email->sendRdvStatutChange($rdv); } catch (\Throwable) {}
            $this->addFlash('success', 'Statut du rendez-vous mis à jour.');
        }
        return $this->redirectToRoute('app_admin_rdv_list');
    }

    // ─── PRODUITS ────────────────────────────────────────────────────────────

    #[Route('/produits', name: 'app_admin_produits')]
    public function produits(ProduitRepository $repo): Response
    {
        return $this->render('admin/produits/index.html.twig', [
            'produits' => $repo->findBy([], ['categorie' => 'ASC', 'nom' => 'ASC']),
        ]);
    }

    #[Route('/produits/nouveau', name: 'app_admin_produit_new')]
    public function produitNew(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $produit, $slugger);
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Produit « ' . $produit->getNom() . ' » créé.');
            return $this->redirectToRoute('app_admin_produits');
        }

        return $this->render('admin/produits/form.html.twig', [
            'form'    => $form,
            'produit' => $produit,
            'titre'   => 'Nouveau produit',
        ]);
    }

    #[Route('/produits/{id}/modifier', name: 'app_admin_produit_edit')]
    public function produitEdit(Produit $produit, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $produit, $slugger);
            $em->flush();
            $this->addFlash('success', 'Produit « ' . $produit->getNom() . ' » modifié.');
            return $this->redirectToRoute('app_admin_produits');
        }

        return $this->render('admin/produits/form.html.twig', [
            'form'    => $form,
            'produit' => $produit,
            'titre'   => 'Modifier : ' . $produit->getNom(),
        ]);
    }

    private function handleImageUpload($form, Produit $produit, SluggerInterface $slugger): void
    {
        $file = $form->get('imageFile')->getData();
        if (!$file) return;

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = $slugger->slug($originalName);
        $fileName = $safeName . '-' . uniqid() . '.' . $file->guessExtension();

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/produits';
        $file->move($uploadDir, $fileName);

        // Supprime l'ancienne image si elle existe
        $old = $produit->getImage();
        if ($old && file_exists($uploadDir . '/' . $old)) {
            unlink($uploadDir . '/' . $old);
        }

        $produit->setImage($fileName);
    }

    #[Route('/produits/{id}/toggle', name: 'app_admin_produit_toggle', methods: ['POST'])]
    public function produitToggle(Produit $produit, EntityManagerInterface $em): Response
    {
        $produit->setActif(!$produit->isActif());
        $em->flush();
        $this->addFlash('success', 'Produit ' . ($produit->isActif() ? 'activé' : 'désactivé') . '.');
        return $this->redirectToRoute('app_admin_produits');
    }

    #[Route('/produits/{id}/supprimer', name: 'app_admin_produit_delete', methods: ['POST'])]
    public function produitDelete(Produit $produit, EntityManagerInterface $em): Response
    {
        $nom = $produit->getNom();
        $em->remove($produit);
        $em->flush();
        $this->addFlash('success', 'Produit « ' . $nom . ' » supprimé.');
        return $this->redirectToRoute('app_admin_produits');
    }

    // ─── COMMANDES ───────────────────────────────────────────────────────────

    #[Route('/commandes', name: 'app_admin_commandes')]
    public function commandes(CommandeRepository $repo): Response
    {
        return $this->render('admin/commandes/index.html.twig', [
            'commandes' => $repo->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/commandes/{id}/statut/{statut}', name: 'app_admin_commande_statut', methods: ['POST'])]
    public function commandeStatut(Commande $commande, string $statut, EntityManagerInterface $em): Response
    {
        $allowed = [Commande::STATUT_PAYEE, Commande::STATUT_EXPEDIEE, Commande::STATUT_LIVREE, Commande::STATUT_ANNULEE];
        if (in_array($statut, $allowed)) {
            $commande->setStatut($statut);
            $em->flush();
            $this->addFlash('success', 'Statut de la commande mis à jour.');
        }
        return $this->redirectToRoute('app_admin_commandes');
    }

    // ─── UTILISATEURS ────────────────────────────────────────────────────────

    #[Route('/utilisateurs', name: 'app_admin_users')]
    public function users(UserRepository $repo): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $repo->findBy([], ['nom' => 'ASC']),
        ]);
    }

    #[Route('/utilisateurs/{id}/toggle-admin', name: 'app_admin_user_toggle_admin', methods: ['POST'])]
    public function userToggleAdmin(User $user, EntityManagerInterface $em): Response
    {
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier votre propre rôle.');
            return $this->redirectToRoute('app_admin_users');
        }

        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles)) {
            $user->setRoles(['ROLE_USER']);
        } else {
            $user->setRoles(['ROLE_ADMIN']);
        }
        $em->flush();
        $this->addFlash('success', 'Rôle mis à jour.');
        return $this->redirectToRoute('app_admin_users');
    }
}
