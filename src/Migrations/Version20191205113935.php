<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191205113935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE consumer (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mailjet_id BIGINT DEFAULT NULL, UNIQUE INDEX UNIQ_705B3727E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consumer_composter (consumer_id INT NOT NULL, composter_id INT NOT NULL, INDEX IDX_B297B0B837FDBD6D (consumer_id), INDEX IDX_B297B0B87E93ED02 (composter_id), PRIMARY KEY(consumer_id, composter_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE consumer_composter ADD CONSTRAINT FK_B297B0B837FDBD6D FOREIGN KEY (consumer_id) REFERENCES consumer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consumer_composter ADD CONSTRAINT FK_B297B0B87E93ED02 FOREIGN KEY (composter_id) REFERENCES composter (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consumer_composter DROP FOREIGN KEY FK_B297B0B837FDBD6D');
        $this->addSql('DROP TABLE consumer');
        $this->addSql('DROP TABLE consumer_composter');
    }
}
