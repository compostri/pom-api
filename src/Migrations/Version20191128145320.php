<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191128145320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, role VARCHAR(255) DEFAULT NULL, contact_type ENUM(\'Syndic\', \'Institution\', \'Établissement scolaire\') NOT NULL COMMENT \'(DC2Type:enumcontacttype)\', UNIQUE INDEX UNIQ_4C62E638E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_composter (contact_id INT NOT NULL, composter_id INT NOT NULL, INDEX IDX_920FB8D7E7A1254A (contact_id), INDEX IDX_920FB8D77E93ED02 (composter_id), PRIMARY KEY(contact_id, composter_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact_composter ADD CONSTRAINT FK_920FB8D7E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_composter ADD CONSTRAINT FK_920FB8D77E93ED02 FOREIGN KEY (composter_id) REFERENCES composter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD phone VARCHAR(255) DEFAULT NULL, ADD role VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement CHANGE capacite capacite VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contact_composter DROP FOREIGN KEY FK_920FB8D7E7A1254A');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE contact_composter');
        $this->addSql('ALTER TABLE equipement CHANGE capacite capacite VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user DROP phone, DROP role');
    }
}
