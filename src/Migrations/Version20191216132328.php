<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191216132328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter ADD nb_foyers_potentiels INT DEFAULT NULL, ADD nb_inscrit INT DEFAULT NULL, ADD nb_deposant INT DEFAULT NULL, ADD signaletique_rond TINYINT(1) DEFAULT NULL, ADD signaletique_panneau TINYINT(1) DEFAULT NULL, ADD has_croc TINYINT(1) DEFAULT NULL, ADD has_cadenas TINYINT(1) DEFAULT NULL, ADD has_fourche TINYINT(1) DEFAULT NULL, ADD has_thermometre TINYINT(1) DEFAULT NULL, ADD has_peson TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE composter DROP nb_foyers_potentiels, DROP nb_inscrit, DROP nb_deposant, DROP signaletique_rond, DROP signaletique_panneau, DROP has_croc, DROP has_cadenas, DROP has_fourche, DROP has_thermometre, DROP has_peson');
    }
}
