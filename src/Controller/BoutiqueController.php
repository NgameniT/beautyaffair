<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoutiqueController extends AbstractController
{
    #[Route('/boutique', name: 'app_boutique')]
    public function index(ProduitRepository $produits, CategorieRepository $categories): Response
    {
        $catPerruque = $categories->findOneBy(['slug' => 'perruques']);
        $catBijou    = $categories->findOneBy(['slug' => 'bijoux']);
        $catSoins    = $categories->findOneBy(['slug' => 'soins-perruques']);

        return $this->render('boutique/index.html.twig', [
            'perruques'      => $produits->findBy(['categorie' => $catPerruque, 'actif' => true], ['nom' => 'ASC']),
            'bijoux'         => $produits->findBy(['categorie' => $catBijou,    'actif' => true], ['nom' => 'ASC']),
            'soinsPerruques' => $catSoins ? $produits->findBy(['categorie' => $catSoins, 'actif' => true], ['nom' => 'ASC']) : [],
        ]);
    }

    #[Route('/boutique/{id}', name: 'app_boutique_show', requirements: ['id' => '\d+'])]
    public function show(Produit $produit, ProduitRepository $produits): Response
    {
        if (!$produit->isActif()) {
            throw $this->createNotFoundException('Produit non disponible.');
        }

        $similaires = $produits->createQueryBuilder('p')
            ->where('p.categorie = :cat AND p.id != :id AND p.actif = true')
            ->setParameter('cat', $produit->getCategorie())
            ->setParameter('id', $produit->getId())
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        return $this->render('boutique/show.html.twig', [
            'produit'    => $produit,
            'similaires' => $similaires,
        ]);
    }
}
