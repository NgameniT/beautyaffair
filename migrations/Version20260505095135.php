<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260505095135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la catégorie Soins Perruques et ses produits d\'entretien';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO categorie (nom, slug, type, icone, description)
            SELECT 'Soins Perruques', 'soins-perruques', 'boutique', '🧴', 'Produits d''entretien et accessoires pour perruques'
            WHERE NOT EXISTS (SELECT 1 FROM categorie WHERE slug = 'soins-perruques')
        ");

        $this->addSql("
            INSERT INTO produit (nom, prix, stock, description, actif, created_at, categorie_id)
            SELECT p.nom, p.prix, p.stock, p.description, true, NOW(), c.id
            FROM (VALUES
                ('Huile Brillance Perruque Lisse', 4500::numeric, 25, 'Huile légère pour entretenir et faire briller les perruques lisses. Sans résidu.'),
                ('Spray Hydratant Curly',          5000::numeric, 20, 'Spray démêlant et hydratant pour perruques bouclées et crêpues. Restitue le volume.'),
                ('Colle Perruque Forte Fixation',  3000::numeric, 30, 'Colle professionnelle longue durée pour la pose de perruques lace et full lace.'),
                ('Bandes Adhésives (lot de 36)',   2500::numeric, 40, 'Bandes double-face pour la pose de perruques. Résistantes à la transpiration.'),
                ('Shampooing Doux Perruque',       4000::numeric, 20, 'Shampooing sans sulfate formulé pour nettoyer délicatement les perruques naturelles et synthétiques.'),
                ('Après-shampooing Démêlant',      3500::numeric, 20, 'Après-shampooing démêlant en profondeur pour restaurer la douceur et la brillance de votre perruque.')
            ) AS p(nom, prix, stock, description)
            JOIN categorie c ON c.slug = 'soins-perruques'
            WHERE NOT EXISTS (SELECT 1 FROM produit WHERE nom = p.nom)
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM produit WHERE categorie_id = (SELECT id FROM categorie WHERE slug = 'soins-perruques')");
        $this->addSql("DELETE FROM categorie WHERE slug = 'soins-perruques'");
    }
}
