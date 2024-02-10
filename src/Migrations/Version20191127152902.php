<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191127152902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter DROP FOREIGN KEY FK_FCFE9BAC64B33AA');
        $this->addSql('CREATE TABLE equipement (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, capacite VARCHAR(255) NOT NULL, UNIQUE INDEX equipement (type, capacite), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE pavilions_volume');
        $this->addSql('DROP INDEX IDX_FCFE9BAC64B33AA ON composter');
        $this->addSql('ALTER TABLE composter CHANGE pavilions_volume_id equipement_id INT DEFAULT NULL');
        $this->addSql('UPDATE composter SET equipement_id = null');
        $this->addSql('ALTER TABLE composter ADD CONSTRAINT FK_FCFE9BAC806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_FCFE9BAC806F0F5C ON composter (equipement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter DROP FOREIGN KEY FK_FCFE9BAC806F0F5C');
        $this->addSql('CREATE TABLE pavilions_volume (id INT AUTO_INCREMENT NOT NULL, volume VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE equipement');
        $this->addSql('DROP INDEX IDX_FCFE9BAC806F0F5C ON composter');
        $this->addSql('ALTER TABLE composter CHANGE equipement_id pavilions_volume_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE composter ADD CONSTRAINT FK_FCFE9BAC64B33AA FOREIGN KEY (pavilions_volume_id) REFERENCES pavilions_volume (id)');
        $this->addSql('CREATE INDEX IDX_FCFE9BAC64B33AA ON composter (pavilions_volume_id)');
    }
}
