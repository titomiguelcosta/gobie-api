<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200627175801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE request (id INT AUTO_INCREMENT NOT NULL, tracking_id INT NOT NULL, query_parameters LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', headers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', method VARCHAR(255) NOT NULL, format VARCHAR(255) NOT NULL, body LONGTEXT DEFAULT NULL, path_info VARCHAR(255) NOT NULL, locale VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_3B978F9F7D05ABBE (tracking_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tracking (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, route_name VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL, terminated_at DATETIME DEFAULT NULL, ip_address VARCHAR(255) DEFAULT NULL, device VARCHAR(255) DEFAULT NULL, navigator VARCHAR(255) DEFAULT NULL, INDEX IDX_A87C621CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE response (id INT AUTO_INCREMENT NOT NULL, tracking_id INT NOT NULL, status_code VARCHAR(3) NOT NULL, status_text VARCHAR(255) NOT NULL, body LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_3E7B0BFB7D05ABBE (tracking_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F7D05ABBE FOREIGN KEY (tracking_id) REFERENCES tracking (id)');
        $this->addSql('ALTER TABLE tracking ADD CONSTRAINT FK_A87C621CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFB7D05ABBE FOREIGN KEY (tracking_id) REFERENCES tracking (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F7D05ABBE');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFB7D05ABBE');
        $this->addSql('DROP TABLE request');
        $this->addSql('DROP TABLE tracking');
        $this->addSql('DROP TABLE response');
    }
}
