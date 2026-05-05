<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProduitFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $perruque = $this->getReference(CategorieFixtures::PERRUQUE_REF, Categorie::class);
        $bijou    = $this->getReference(CategorieFixtures::BIJOU_REF, Categorie::class);

        $produits = [
            // Perruques
            ['Perruque Lisse Naturelle',       75000, 8,  'Cheveux naturels 100%, longueur 20 pouces, lissage parfait.',       $perruque],
            ['Perruque Bouclée Synthétique',   35000, 15, 'Synthétique haute qualité, boucles légères et naturelles.',         $perruque],
            ['Extensions Brésiliennes',        55000, 20, 'Mèches brésiliennes naturelles, 3 paquets, longueur au choix.',      $perruque],
            ['Perruque Courte Bob',            28000, 12, 'Style bob net et élégant, naturel ou synthétique.',                  $perruque],
            ['Closure Lace 4×4',               40000, 10, 'Closure lace frontale naturelle pour un look réaliste.',             $perruque],
            ['Mèches Crochet Dreadlocks',      12000, 30, 'Mèches synthétiques pour locks et tresses au crochet.',             $perruque],

            // Bijoux
            ['Collier Doré Élégant',            8500, 25, 'Collier plaqué or avec pendentif, idéal pour soirées.',             $bijou],
            ['Set Boucles d\'Oreilles x3',      5000, 40, 'Lot de 3 paires assorties : perles, créoles et puces dorées.',      $bijou],
            ['Bracelet Fin Plaqué Or',          3500, 35, 'Bracelet délicat plaqué or, doré et rosé disponibles.',             $bijou],
            ['Parure Complète',                15000, 10, 'Set collier + boucles + bracelet assortis, parfait en cadeau.',      $bijou],
            ['Barrettes & Épingles',            2500, 50, 'Accessoires cheveux tendance : barrettes perlées, épingles fleurs.', $bijou],
            ['Chaîne de Cheville',              3000, 30, 'Chaîne fine réglable plaqué or, parfaite pour l\'été.',             $bijou],
        ];

        foreach ($produits as [$nom, $prix, $stock, $desc, $cat]) {
            $p = new Produit();
            $p->setNom($nom)
              ->setPrix((string) $prix)
              ->setStock($stock)
              ->setDescription($desc)
              ->setCategorie($cat)
              ->setActif(true);
            $manager->persist($p);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CategorieFixtures::class];
    }
}
