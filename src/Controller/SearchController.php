<?php

namespace App\Controller;

use App\Repository\PrestationRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    #[Route('/recherche', name: 'app_search', methods: ['GET'])]
    public function index(
        Request              $request,
        ProduitRepository    $produits,
        PrestationRepository $prestations,
    ): Response {
        $q = trim($request->query->get('q', ''));

        if (strlen($q) < 2) {
            return $this->render('search/index.html.twig', [
                'q'           => $q,
                'produits'    => [],
                'prestations' => [],
            ]);
        }

        $like = '%' . mb_strtolower($q) . '%';

        $resProduits = $produits->createQueryBuilder('p')
            ->join('p.categorie', 'c')
            ->where('p.actif = true')
            ->andWhere('LOWER(p.nom) LIKE :q OR LOWER(p.description) LIKE :q')
            ->setParameter('q', $like)
            ->orderBy('c.nom', 'ASC')
            ->addOrderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();

        $resPrestations = $prestations->createQueryBuilder('p')
            ->join('p.categorie', 'c')
            ->where('p.actif = true')
            ->andWhere('LOWER(p.nom) LIKE :q')
            ->setParameter('q', $like)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('search/index.html.twig', [
            'q'           => $q,
            'produits'    => $resProduits,
            'prestations' => $resPrestations,
        ]);
    }
}
