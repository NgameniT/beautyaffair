<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Prestation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PrestationFixtures extends Fixture implements DependentFixtureInterface
{
    public const PREFIX = 'prestation_';

    public function load(ObjectManager $manager): void
    {
        $femme     = $this->getReference(CategorieFixtures::FEMME_REF,     Categorie::class);
        $homme     = $this->getReference(CategorieFixtures::HOMME_REF,     Categorie::class);
        $enfant    = $this->getReference(CategorieFixtures::ENFANT_REF,    Categorie::class);
        $ongles    = $this->getReference(CategorieFixtures::ONGLES_REF,    Categorie::class);
        $entretien = $this->getReference(CategorieFixtures::ENTRETIEN_REF, Categorie::class);

        $data = [
            // [nom, prix, durée(min), catégorie]
            ['Coupe + Brushing',          15000, 60,  $femme],
            ['Tresses simples',           10000, 90,  $femme],
            ['Nattes collées',            20000, 150, $femme],
            ['Coloration complète',       25000, 120, $femme],
            ['Défrisage',                 18000, 90,  $femme],
            ['Soin capillaire',            8000, 45,  $femme],
            ['Tissage / Extensions',      30000, 180, $femme],

            ['Coupe simple',               5000, 30,  $homme],
            ['Dégradé américain',          7000, 40,  $homme],
            ['Dégradé + barbe',           10000, 50,  $homme],
            ['Coupe + traits',             8500, 45,  $homme],
            ['Rasage à blanc',             4000, 25,  $homme],
            ['Taille de barbe seule',      3000, 20,  $homme],
            ['Soin & coloration',         12000, 60,  $homme],

            ['Coupe garçon simple',        3000, 25,  $enfant],
            ['Coupe garçon + traits',      4500, 35,  $enfant],
            ['Coupe fille + brushing',     5000, 40,  $enfant],
            ['Tresses enfant',             8000, 75,  $enfant],
            ['Nattes enfant',             12000, 120, $enfant],
            ['Soin capillaire enfant',     5000, 40,  $enfant],

            ['Manucure simple',            5000, 30,  $ongles],
            ['Manucure gel',               8000, 45,  $ongles],
            ['Pose faux ongles',          12000, 60,  $ongles],
            ['Pédicure simple',            5000, 35,  $ongles],
            ['Pédicure gel',               8000, 50,  $ongles],
            ['Manucure + Pédicure',       15000, 75,  $ongles],

            ['Lavage + conditionnement',   8000, 45,  $entretien],
            ['Défrisage sur perruque',    10000, 60,  $entretien],
            ['Brushing sur perruque',      6000, 40,  $entretien],
            ['Remise à neuf complète',    15000, 90,  $entretien],
            ['Teinture sur perruque',     18000, 90,  $entretien],
            ['Réparation trame/dentelle', 12000, 60,  $entretien],
        ];

        foreach ($data as $i => [$nom, $prix, $duree, $cat]) {
            $p = new Prestation();
            $p->setNom($nom)
              ->setPrix((string) $prix)
              ->setDuree($duree)
              ->setCategorie($cat)
              ->setActif(true);
            $manager->persist($p);
            $this->addReference(self::PREFIX.$i, $p);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CategorieFixtures::class];
    }
}
