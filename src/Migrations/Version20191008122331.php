<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191008122331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter ADD mc_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE composter ADD CONSTRAINT FK_FCFE9BAC1713002E FOREIGN KEY (mc_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_FCFE9BAC1713002E ON composter (mc_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter DROP FOREIGN KEY FK_FCFE9BAC1713002E');
        $this->addSql('DROP INDEX IDX_FCFE9BAC1713002E ON composter');
        $this->addSql('ALTER TABLE composter DROP mc_id');
    }
}
