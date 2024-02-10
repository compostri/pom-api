<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191119091414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE reparation_media_object (reparation_id INT NOT NULL, media_object_id INT NOT NULL, INDEX IDX_15F3694E97934BA (reparation_id), INDEX IDX_15F3694E64DE5A5 (media_object_id), PRIMARY KEY(reparation_id, media_object_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reparation_media_object ADD CONSTRAINT FK_15F3694E97934BA FOREIGN KEY (reparation_id) REFERENCES reparation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reparation_media_object ADD CONSTRAINT FK_15F3694E64DE5A5 FOREIGN KEY (media_object_id) REFERENCES media_object (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE reparation_media_object');
    }
}
