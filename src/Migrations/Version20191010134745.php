<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191010134745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE approvisionnement_broyat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE composter ADD approvisionnement_broyat_id INT DEFAULT NULL, ADD cadena VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE composter ADD CONSTRAINT FK_FCFE9BACF2BD5D81 FOREIGN KEY (approvisionnement_broyat_id) REFERENCES approvisionnement_broyat (id)');
        $this->addSql('CREATE INDEX IDX_FCFE9BACF2BD5D81 ON composter (approvisionnement_broyat_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter DROP FOREIGN KEY FK_FCFE9BACF2BD5D81');
        $this->addSql('DROP TABLE approvisionnement_broyat');
        $this->addSql('DROP INDEX IDX_FCFE9BACF2BD5D81 ON composter');
        $this->addSql('ALTER TABLE composter DROP approvisionnement_broyat_id, DROP cadena');
    }
}
