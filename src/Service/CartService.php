<?php

namespace App\Service;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    public function __construct(
        private readonly RequestStack    $requestStack,
        private readonly ProduitRepository $produits,
    ) {}

    public function add(int $id, int $qty = 1): void
    {
        $cart       = $this->getRaw();
        $cart[$id]  = ($cart[$id] ?? 0) + $qty;
        $this->save($cart);
    }

    public function updateQty(int $id, int $qty): void
    {
        $cart = $this->getRaw();
        if ($qty <= 0) {
            unset($cart[$id]);
        } else {
            $cart[$id] = $qty;
        }
        $this->save($cart);
    }

    public function remove(int $id): void
    {
        $cart = $this->getRaw();
        unset($cart[$id]);
        $this->save($cart);
    }

    public function clear(): void
    {
        $this->save([]);
    }

    /** Retourne les lignes du panier avec l'objet Produit hydraté */
    public function getItems(): array
    {
        $items = [];
        foreach ($this->getRaw() as $id => $qty) {
            $produit = $this->produits->find($id);
            if ($produit && $produit->isActif()) {
                $items[] = [
                    'produit'    => $produit,
                    'quantite'   => $qty,
                    'sous_total' => (float) $produit->getPrix() * $qty,
                ];
            }
        }
        return $items;
    }

    public function getTotal(): float
    {
        return array_sum(array_column($this->getItems(), 'sous_total'));
    }

    public function getCount(): int
    {
        return (int) array_sum($this->getRaw());
    }

    public function getRaw(): array
    {
        return $this->requestStack->getSession()->get('cart', []);
    }

    private function save(array $cart): void
    {
        $this->requestStack->getSession()->set('cart', $cart);
    }
}
