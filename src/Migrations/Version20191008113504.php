<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191008113504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pavilions_volume (id INT AUTO_INCREMENT NOT NULL, volume VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE composter ADD pavilions_volume_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE composter ADD CONSTRAINT FK_FCFE9BAC64B33AA FOREIGN KEY (pavilions_volume_id) REFERENCES pavilions_volume (id)');
        $this->addSql('CREATE INDEX IDX_FCFE9BAC64B33AA ON composter (pavilions_volume_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter DROP FOREIGN KEY FK_FCFE9BAC64B33AA');
        $this->addSql('DROP TABLE pavilions_volume');
        $this->addSql('DROP INDEX IDX_FCFE9BAC64B33AA ON composter');
        $this->addSql('ALTER TABLE composter DROP pavilions_volume_id');
    }
}
