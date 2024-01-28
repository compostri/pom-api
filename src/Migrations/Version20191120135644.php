<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191120135644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter ADD broyat_level ENUM(\'Empty\', \'Reserve\', \'Full\') DEFAULT \'Full\' NOT NULL COMMENT \'(DC2Type:enumbroyat)\', CHANGE accept_new_members accept_new_members TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter DROP broyat_level, CHANGE accept_new_members accept_new_members ENUM(\'Empty\', \'Reserve\', \'Full\') CHARACTER SET utf8mb4 DEFAULT \'Full\' NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:enumbroyat)\'');
    }
}
