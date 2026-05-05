<?php

namespace App\Service;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class FavorisService
{
    private const SESSION_KEY = 'favoris';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProduitRepository $produitRepository,
    ) {}

    private function session(): \Symfony\Component\HttpFoundation\Session\SessionInterface
    {
        return $this->requestStack->getSession();
    }

    private function getRaw(): array
    {
        return $this->session()->get(self::SESSION_KEY, []);
    }

    public function toggle(int $id): bool
    {
        $ids = $this->getRaw();
        $key = array_search($id, $ids);

        if ($key !== false) {
            unset($ids[$key]);
            $this->session()->set(self::SESSION_KEY, array_values($ids));
            return false; // retiré
        }

        $ids[] = $id;
        $this->session()->set(self::SESSION_KEY, $ids);
        return true; // ajouté
    }

    public function has(int $id): bool
    {
        return in_array($id, $this->getRaw(), true);
    }

    public function getIds(): array
    {
        return $this->getRaw();
    }

    public function getCount(): int
    {
        return count($this->getRaw());
    }

    /** @return Produit[] */
    public function getProduits(): array
    {
        $ids = $this->getRaw();
        if (empty($ids)) {
            return [];
        }
        return $this->produitRepository->findBy(['id' => $ids]);
    }
}
