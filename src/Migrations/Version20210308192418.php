<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210308192418 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE check_run (id INT AUTO_INCREMENT NOT NULL, job_id INT NOT NULL, username VARCHAR(255) NOT NULL, repo VARCHAR(255) NOT NULL, check_id VARCHAR(255) NOT NULL, instalation_id VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5BC280DBE04EA9 (job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE check_run ADD CONSTRAINT FK_5BC280DBE04EA9 FOREIGN KEY (job_id) REFERENCES job (id)');
        $this->addSql('ALTER TABLE job ADD check_run_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8F113076B FOREIGN KEY (check_run_id) REFERENCES check_run (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FBD8E0F8F113076B ON job (check_run_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F8F113076B');
        $this->addSql('DROP TABLE check_run');
        $this->addSql('DROP INDEX UNIQ_FBD8E0F8F113076B ON job');
        $this->addSql('ALTER TABLE job DROP check_run_id');
    }
}
