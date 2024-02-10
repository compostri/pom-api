<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191120165127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter DROP FOREIGN KEY FK_FCFE9BAC3DA5256D');
        $this->addSql('ALTER TABLE composter ADD CONSTRAINT FK_FCFE9BAC3DA5256D FOREIGN KEY (image_id) REFERENCES media_object (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter DROP FOREIGN KEY FK_FCFE9BAC3DA5256D');
        $this->addSql('ALTER TABLE composter ADD CONSTRAINT FK_FCFE9BAC3DA5256D FOREIGN KEY (image_id) REFERENCES media_object (id)');
    }
}
