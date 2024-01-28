<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191008094013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE commune (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quartier (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pole (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE composter ADD commune_id INT DEFAULT NULL, ADD pole_id INT DEFAULT NULL, ADD quartier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE composter ADD CONSTRAINT FK_FCFE9BAC131A4F72 FOREIGN KEY (commune_id) REFERENCES commune (id)');
        $this->addSql('ALTER TABLE composter ADD CONSTRAINT FK_FCFE9BAC419C3385 FOREIGN KEY (pole_id) REFERENCES pole (id)');
        $this->addSql('ALTER TABLE composter ADD CONSTRAINT FK_FCFE9BACDF1E57AB FOREIGN KEY (quartier_id) REFERENCES quartier (id)');
        $this->addSql('CREATE INDEX IDX_FCFE9BAC131A4F72 ON composter (commune_id)');
        $this->addSql('CREATE INDEX IDX_FCFE9BAC419C3385 ON composter (pole_id)');
        $this->addSql('CREATE INDEX IDX_FCFE9BACDF1E57AB ON composter (quartier_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter DROP FOREIGN KEY FK_FCFE9BAC131A4F72');
        $this->addSql('ALTER TABLE composter DROP FOREIGN KEY FK_FCFE9BACDF1E57AB');
        $this->addSql('ALTER TABLE composter DROP FOREIGN KEY FK_FCFE9BAC419C3385');
        $this->addSql('DROP TABLE commune');
        $this->addSql('DROP TABLE quartier');
        $this->addSql('DROP TABLE pole');
        $this->addSql('DROP INDEX IDX_FCFE9BAC131A4F72 ON composter');
        $this->addSql('DROP INDEX IDX_FCFE9BAC419C3385 ON composter');
        $this->addSql('DROP INDEX IDX_FCFE9BACDF1E57AB ON composter');
        $this->addSql('ALTER TABLE composter DROP commune_id, DROP pole_id, DROP quartier_id');
    }
}
