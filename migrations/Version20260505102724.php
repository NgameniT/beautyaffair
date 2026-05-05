<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260505102724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les catégories Enfants, Manucure/Pédicure, Entretien Perruques et leurs prestations';
    }

    public function up(Schema $schema): void
    {
        // Nouvelles catégories de service
        $this->addSql("
            INSERT INTO categorie (nom, slug, type, icone, description)
            SELECT * FROM (VALUES
                ('Coiffure Enfants',      'coiffure-enfants',      'coiffure', '🧒', 'Coupes, tresses et nattes pour enfants filles et garçons'),
                ('Manucure & Pédicure',   'manucure-pedicure',     'coiffure', '💅', 'Soin des ongles mains et pieds, pose gel et faux ongles'),
                ('Entretien Perruques',   'entretien-perruques',   'coiffure', '✨', 'Remise à neuf, lavage et réparation de perruques')
            ) AS v(nom, slug, type, icone, description)
            WHERE NOT EXISTS (SELECT 1 FROM categorie WHERE slug = v.slug)
        ");

        // Prestations Coiffure Enfants
        $this->addSql("
            INSERT INTO prestation (nom, prix, duree, actif, categorie_id)
            SELECT p.nom, p.prix, p.duree, true, c.id
            FROM (VALUES
                ('Coupe garçon simple',      3000::numeric, 25),
                ('Coupe garçon + traits',    4500::numeric, 35),
                ('Coupe fille + brushing',   5000::numeric, 40),
                ('Tresses enfant',           8000::numeric, 75),
                ('Nattes enfant',           12000::numeric, 120),
                ('Soin capillaire enfant',   5000::numeric, 40)
            ) AS p(nom, prix, duree)
            JOIN categorie c ON c.slug = 'coiffure-enfants'
            WHERE NOT EXISTS (SELECT 1 FROM prestation WHERE nom = p.nom)
        ");

        // Prestations Manucure & Pédicure
        $this->addSql("
            INSERT INTO prestation (nom, prix, duree, actif, categorie_id)
            SELECT p.nom, p.prix, p.duree, true, c.id
            FROM (VALUES
                ('Manucure simple',       5000::numeric, 30),
                ('Manucure gel',          8000::numeric, 45),
                ('Pose faux ongles',     12000::numeric, 60),
                ('Pédicure simple',       5000::numeric, 35),
                ('Pédicure gel',          8000::numeric, 50),
                ('Manucure + Pédicure',  15000::numeric, 75)
            ) AS p(nom, prix, duree)
            JOIN categorie c ON c.slug = 'manucure-pedicure'
            WHERE NOT EXISTS (SELECT 1 FROM prestation WHERE nom = p.nom)
        ");

        // Prestations Entretien Perruques
        $this->addSql("
            INSERT INTO prestation (nom, prix, duree, actif, categorie_id)
            SELECT p.nom, p.prix, p.duree, true, c.id
            FROM (VALUES
                ('Lavage + conditionnement',       8000::numeric, 45),
                ('Défrisage sur perruque',        10000::numeric, 60),
                ('Brushing sur perruque',          6000::numeric, 40),
                ('Remise à neuf complète',        15000::numeric, 90),
                ('Teinture sur perruque',         18000::numeric, 90),
                ('Réparation trame/dentelle',     12000::numeric, 60)
            ) AS p(nom, prix, duree)
            JOIN categorie c ON c.slug = 'entretien-perruques'
            WHERE NOT EXISTS (SELECT 1 FROM prestation WHERE nom = p.nom)
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM prestation WHERE categorie_id IN (SELECT id FROM categorie WHERE slug IN ('coiffure-enfants','manucure-pedicure','entretien-perruques'))");
        $this->addSql("DELETE FROM categorie WHERE slug IN ('coiffure-enfants','manucure-pedicure','entretien-perruques')");
    }
}
