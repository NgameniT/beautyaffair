<?php

namespace App\Controller;

use App\Service\FavorisService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/favoris')]
final class FavorisController extends AbstractController
{
    #[Route('', name: 'app_favoris', methods: ['GET'])]
    public function index(FavorisService $favoris): Response
    {
        return $this->render('favoris/index.html.twig', [
            'produits' => $favoris->getProduits(),
        ]);
    }

    #[Route('/toggle/{id}', name: 'app_favoris_toggle', methods: ['POST'])]
    public function toggle(int $id, FavorisService $favoris, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('fav', $request->request->get('_token', ''))) {
            return $this->json(['error' => 'Token invalide'], 403);
        }

        $ajoute = $favoris->toggle($id);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'added' => $ajoute,
                'count' => $favoris->getCount(),
            ]);
        }

        $this->addFlash('success', $ajoute ? 'Ajouté aux favoris ♥' : 'Retiré des favoris.');
        return $this->redirect($request->headers->get('referer', $this->generateUrl('app_boutique')));
    }
}
