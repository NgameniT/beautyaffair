<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Service\FavorisService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoutiqueController extends AbstractController
{
    #[Route('/boutique', name: 'app_boutique')]
    public function index(ProduitRepository $produits, CategorieRepository $categories, FavorisService $favoris): Response
    {
        $catPerruque = $categories->findOneBy(['slug' => 'perruques']);
        $catBijou    = $categories->findOneBy(['slug' => 'bijoux']);

        return $this->render('boutique/index.html.twig', [
            'perruques'   => $produits->findBy(['categorie' => $catPerruque, 'actif' => true], ['nom' => 'ASC']),
            'bijoux'      => $produits->findBy(['categorie' => $catBijou,    'actif' => true], ['nom' => 'ASC']),
            'favorisIds'  => $favoris->getIds(),
        ]);
    }

    #[Route('/boutique/{id}', name: 'app_boutique_show', requirements: ['id' => '\d+'])]
    public function show(Produit $produit, ProduitRepository $produits, FavorisService $favoris): Response
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
            'isFavori'   => $favoris->has($produit->getId()),
            'favorisIds' => $favoris->getIds(),
        ]);
    }
}
