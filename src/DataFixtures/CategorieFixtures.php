<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixtures extends Fixture
{
    public const FEMME_REF    = 'cat_coiffure_femme';
    public const HOMME_REF    = 'cat_coiffure_homme';
    public const PERRUQUE_REF = 'cat_perruques';
    public const BIJOU_REF    = 'cat_bijoux';

    public function load(ObjectManager $manager): void
    {
        $cats = [
            [self::FEMME_REF,    'Coiffure Femme',  'coiffure-femme',  'coiffure', '💇‍♀️', 'Coupe, tresses, nattes, coloration et soins capillaires pour femmes'],
            [self::HOMME_REF,    'Coiffure Homme',  'coiffure-homme',  'coiffure', '💇‍♂️', 'Coupe, dégradé, barbe et soins capillaires pour hommes'],
            [self::PERRUQUE_REF, 'Perruques',       'perruques',       'boutique', '👱‍♀️', 'Perruques naturelles et synthétiques de haute qualité'],
            [self::BIJOU_REF,    'Bijoux',           'bijoux',          'boutique', '💍', 'Colliers, boucles d\'oreilles, bracelets et parures'],
        ];

        foreach ($cats as [$ref, $nom, $slug, $type, $icone, $desc]) {
            $cat = new Categorie();
            $cat->setNom($nom)
                ->setSlug($slug)
                ->setType($type)
                ->setIcone($icone)
                ->setDescription($desc);
            $manager->persist($cat);
            $this->addReference($ref, $cat);
        }

        $manager->flush();
    }
}
