<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191011085817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE reparation (id INT AUTO_INCREMENT NOT NULL, composter_id INT NOT NULL, date DATETIME DEFAULT NULL, done TINYINT(1) NOT NULL, description LONGTEXT DEFAULT NULL, ref_facture VARCHAR(255) DEFAULT NULL, montant DOUBLE PRECISION DEFAULT NULL, INDEX IDX_8FDF219D7E93ED02 (composter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reparation ADD CONSTRAINT FK_8FDF219D7E93ED02 FOREIGN KEY (composter_id) REFERENCES composter (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE reparation');
    }
}
