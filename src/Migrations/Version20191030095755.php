<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191030095755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE composter SET `status` = \'Active\'');
        $this->addSql('ALTER TABLE composter CHANGE status status ENUM(\'Active\', \'Delete\', \'Moved\', \'ToBeMoved\', \'Dormant\', \'InProject\') DEFAULT \'Active\' NOT NULL COMMENT \'(DC2Type:enumstatus)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter CHANGE status status VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
