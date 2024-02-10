<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191127144525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE financeur (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, initials VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE composter ADD financeur_id INT DEFAULT NULL, ADD serial_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE composter ADD CONSTRAINT FK_FCFE9BAC5D17678C FOREIGN KEY (financeur_id) REFERENCES financeur (id)');
        $this->addSql('CREATE INDEX IDX_FCFE9BAC5D17678C ON composter (financeur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter DROP FOREIGN KEY FK_FCFE9BAC5D17678C');
        $this->addSql('DROP TABLE financeur');
        $this->addSql('DROP INDEX IDX_FCFE9BAC5D17678C ON composter');
        $this->addSql('ALTER TABLE composter DROP financeur_id, DROP serial_number');
    }
}
