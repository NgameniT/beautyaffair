<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixtures extends Fixture
{
    public const FEMME_REF     = 'cat_coiffure_femme';
    public const HOMME_REF     = 'cat_coiffure_homme';
    public const ENFANT_REF    = 'cat_coiffure_enfants';
    public const ONGLES_REF    = 'cat_manucure_pedicure';
    public const ENTRETIEN_REF = 'cat_entretien_perruques';
    public const PERRUQUE_REF  = 'cat_perruques';
    public const BIJOU_REF     = 'cat_bijoux';
    public const SOINS_REF     = 'cat_soins_perruques';

    public function load(ObjectManager $manager): void
    {
        $cats = [
            [self::FEMME_REF,    'Coiffure Femme',      'coiffure-femme',      'coiffure', '💇‍♀️', 'Coupe, tresses, nattes, coloration et soins capillaires pour femmes'],
            [self::HOMME_REF,    'Coiffure Homme',      'coiffure-homme',      'coiffure', '💇‍♂️', 'Coupe, dégradé, barbe et soins capillaires pour hommes'],
            [self::ENFANT_REF,   'Coiffure Enfants',    'coiffure-enfants',    'coiffure', '🧒',   'Coupes, tresses et nattes pour enfants filles et garçons'],
            [self::ONGLES_REF,   'Manucure & Pédicure', 'manucure-pedicure',   'coiffure', '💅',   'Soin des ongles mains et pieds, pose gel et faux ongles'],
            [self::ENTRETIEN_REF,'Entretien Perruques', 'entretien-perruques', 'coiffure', '✨',   'Remise à neuf, lavage et réparation de perruques'],
            [self::PERRUQUE_REF, 'Perruques',           'perruques',           'boutique', '👱‍♀️', 'Perruques naturelles et synthétiques de haute qualité'],
            [self::BIJOU_REF,    'Bijoux',              'bijoux',              'boutique', '💍',   'Colliers, boucles d\'oreilles, bracelets et parures'],
            [self::SOINS_REF,    'Soins Perruques',     'soins-perruques',     'boutique', '🧴',   'Produits d\'entretien et accessoires pour perruques'],
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
