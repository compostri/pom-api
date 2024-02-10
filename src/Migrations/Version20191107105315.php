<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191107105315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE composter_contact (id INT AUTO_INCREMENT NOT NULL, composter_id INT NOT NULL, email VARCHAR(180) NOT NULL, message LONGTEXT NOT NULL, sent_by_mailjet TINYINT(1) DEFAULT NULL, creation_date DATETIME NOT NULL, INDEX IDX_8A5B12757E93ED02 (composter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE composter_contact ADD CONSTRAINT FK_8A5B12757E93ED02 FOREIGN KEY (composter_id) REFERENCES composter (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE composter_contact');
    }
}
