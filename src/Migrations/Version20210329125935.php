<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210329125935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD is_subscribe_to_compostri_newsletter TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE has_formation_referent_site has_formation_referent_site TINYINT(1) DEFAULT \'0\', CHANGE has_formation_guide_composteur has_formation_guide_composteur TINYINT(1) DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP is_subscribe_to_compostri_newsletter, CHANGE has_formation_referent_site has_formation_referent_site TINYINT(1) DEFAULT NULL, CHANGE has_formation_guide_composteur has_formation_guide_composteur TINYINT(1) DEFAULT NULL');
    }
}
