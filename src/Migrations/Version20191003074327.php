<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191003074327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_permanence (user_id INT NOT NULL, permanence_id INT NOT NULL, INDEX IDX_78570D5AA76ED395 (user_id), INDEX IDX_78570D5AA9457964 (permanence_id), PRIMARY KEY(user_id, permanence_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permanence (id INT AUTO_INCREMENT NOT NULL, composter_id INT DEFAULT NULL, date DATETIME NOT NULL, canceled TINYINT(1) NOT NULL, event_title VARCHAR(255) DEFAULT NULL, event_message LONGTEXT DEFAULT NULL, nb_users SMALLINT DEFAULT NULL, nb_buckets DOUBLE PRECISION DEFAULT NULL, temperature DOUBLE PRECISION DEFAULT NULL, has_users_been_notify TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_DF30CBB67E93ED02 (composter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE composter (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, short_description LONGTEXT NOT NULL, description LONGTEXT NOT NULL, address LONGTEXT NOT NULL, lat DOUBLE PRECISION NOT NULL, lng DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_permanence ADD CONSTRAINT FK_78570D5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_permanence ADD CONSTRAINT FK_78570D5AA9457964 FOREIGN KEY (permanence_id) REFERENCES permanence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE permanence ADD CONSTRAINT FK_DF30CBB67E93ED02 FOREIGN KEY (composter_id) REFERENCES composter (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_permanence DROP FOREIGN KEY FK_78570D5AA76ED395');
        $this->addSql('ALTER TABLE user_permanence DROP FOREIGN KEY FK_78570D5AA9457964');
        $this->addSql('ALTER TABLE permanence DROP FOREIGN KEY FK_DF30CBB67E93ED02');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_permanence');
        $this->addSql('DROP TABLE permanence');
        $this->addSql('DROP TABLE composter');
    }
}
