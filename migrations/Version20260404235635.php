<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260404235635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_bf41ce2aa76ed395 RENAME TO IDX_DC4E460BA76ED395');
        $this->addSql('ALTER INDEX idx_bf41ce2a16a2b381 RENAME TO IDX_DC4E460B16A2B381');
        $this->addSql('ALTER INDEX idx_14a41907a76ed395 RENAME TO IDX_8FBBAD54A76ED395');
        $this->addSql('ALTER INDEX idx_14a4190771f7e88b RENAME TO IDX_8FBBAD5471F7E88B');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_dc4e460b16a2b381 RENAME TO idx_bf41ce2a16a2b381');
        $this->addSql('ALTER INDEX idx_dc4e460ba76ed395 RENAME TO idx_bf41ce2aa76ed395');
        $this->addSql('ALTER INDEX idx_8fbbad54a76ed395 RENAME TO idx_14a41907a76ed395');
        $this->addSql('ALTER INDEX idx_8fbbad5471f7e88b RENAME TO idx_14a4190771f7e88b');
    }
}
