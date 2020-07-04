<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200704095002 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE project_audit (id INT UNSIGNED AUTO_INCREMENT NOT NULL, type VARCHAR(10) NOT NULL, object_id VARCHAR(255) NOT NULL, discriminator VARCHAR(255) DEFAULT NULL, transaction_hash VARCHAR(40) DEFAULT NULL, diffs JSON DEFAULT NULL, blame_id VARCHAR(255) DEFAULT NULL, blame_user VARCHAR(255) DEFAULT NULL, blame_user_fqdn VARCHAR(255) DEFAULT NULL, blame_user_firewall VARCHAR(100) DEFAULT NULL, ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX type_992209a91d7383e3271f9ba253d4ee83_idx (type), INDEX object_id_992209a91d7383e3271f9ba253d4ee83_idx (object_id), INDEX discriminator_992209a91d7383e3271f9ba253d4ee83_idx (discriminator), INDEX transaction_hash_992209a91d7383e3271f9ba253d4ee83_idx (transaction_hash), INDEX blame_id_992209a91d7383e3271f9ba253d4ee83_idx (blame_id), INDEX created_at_992209a91d7383e3271f9ba253d4ee83_idx (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_audit (id INT UNSIGNED AUTO_INCREMENT NOT NULL, type VARCHAR(10) NOT NULL, object_id VARCHAR(255) NOT NULL, discriminator VARCHAR(255) DEFAULT NULL, transaction_hash VARCHAR(40) DEFAULT NULL, diffs JSON DEFAULT NULL, blame_id VARCHAR(255) DEFAULT NULL, blame_user VARCHAR(255) DEFAULT NULL, blame_user_fqdn VARCHAR(255) DEFAULT NULL, blame_user_firewall VARCHAR(100) DEFAULT NULL, ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX type_085ea742d6c939af0d0028627f20fc6d_idx (type), INDEX object_id_085ea742d6c939af0d0028627f20fc6d_idx (object_id), INDEX discriminator_085ea742d6c939af0d0028627f20fc6d_idx (discriminator), INDEX transaction_hash_085ea742d6c939af0d0028627f20fc6d_idx (transaction_hash), INDEX blame_id_085ea742d6c939af0d0028627f20fc6d_idx (blame_id), INDEX created_at_085ea742d6c939af0d0028627f20fc6d_idx (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_audit (id INT UNSIGNED AUTO_INCREMENT NOT NULL, type VARCHAR(10) NOT NULL, object_id VARCHAR(255) NOT NULL, discriminator VARCHAR(255) DEFAULT NULL, transaction_hash VARCHAR(40) DEFAULT NULL, diffs JSON DEFAULT NULL, blame_id VARCHAR(255) DEFAULT NULL, blame_user VARCHAR(255) DEFAULT NULL, blame_user_fqdn VARCHAR(255) DEFAULT NULL, blame_user_firewall VARCHAR(100) DEFAULT NULL, ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX type_1e07a667b817a6f7c915861129f79432_idx (type), INDEX object_id_1e07a667b817a6f7c915861129f79432_idx (object_id), INDEX discriminator_1e07a667b817a6f7c915861129f79432_idx (discriminator), INDEX transaction_hash_1e07a667b817a6f7c915861129f79432_idx (transaction_hash), INDEX blame_id_1e07a667b817a6f7c915861129f79432_idx (blame_id), INDEX created_at_1e07a667b817a6f7c915861129f79432_idx (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE project_audit');
        $this->addSql('DROP TABLE task_audit');
        $this->addSql('DROP TABLE job_audit');
    }
}
