<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191218141737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE livraison_broyat ADD livreur_id INT DEFAULT NULL, DROP unite, DROP livreur');
        $this->addSql('ALTER TABLE livraison_broyat ADD CONSTRAINT FK_4D62E3FEF8646701 FOREIGN KEY (livreur_id) REFERENCES approvisionnement_broyat (id)');
        $this->addSql('CREATE INDEX IDX_4D62E3FEF8646701 ON livraison_broyat (livreur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE livraison_broyat DROP FOREIGN KEY FK_4D62E3FEF8646701');
        $this->addSql('DROP INDEX IDX_4D62E3FEF8646701 ON livraison_broyat');
        $this->addSql('ALTER TABLE livraison_broyat ADD unite VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD livreur VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP livreur_id');
    }
}
