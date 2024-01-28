<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201210102244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_composter CHANGE capability capability ENUM(\'Referent\', \'Opener\', \'User\') DEFAULT \'User\' NOT NULL COMMENT \'(DC2Type:enumcapability)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_composter CHANGE capability capability ENUM(\'Referent\', \'Opener\', \'User\') CHARACTER SET utf8mb4 DEFAULT \'Opener\' NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:enumcapability)\'');
    }
}
