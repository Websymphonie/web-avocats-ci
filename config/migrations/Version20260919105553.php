<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260919105553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account_activations (selector VARCHAR(32) NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL, consumed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_434BFF79692E25D (selector), INDEX IDX_434BFF7A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE auth_log (auth_attempt_at DATETIME DEFAULT NULL, user_ip VARCHAR(20) DEFAULT NULL, email_entered VARCHAR(255) NOT NULL, is_success_ful_auth TINYINT DEFAULT 0 NOT NULL, start_of_black_listing DATETIME DEFAULT NULL, end_of_black_listing DATETIME DEFAULT NULL, is_remember_me_auth TINYINT DEFAULT 0 NOT NULL, deauthenticated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1DD25DB8D17F50A6 (uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE currencies (currency_code VARCHAR(10) DEFAULT NULL, currency_name VARCHAR(30) NOT NULL, left_symbol VARCHAR(12) DEFAULT NULL, right_symbol VARCHAR(12) DEFAULT NULL, decimal_symbol VARCHAR(1) DEFAULT NULL, decimal_place INT DEFAULT NULL, thousands_separator VARCHAR(1) DEFAULT NULL, exchanged_value DOUBLE PRECISION DEFAULT NULL, codeiso INT DEFAULT NULL, lang VARCHAR(255) DEFAULT NULL, lang_code VARCHAR(255) DEFAULT NULL, is_active TINYINT DEFAULT 0 NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE images (name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, filename VARCHAR(255) DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE logs (message LONGTEXT NOT NULL, context JSON NOT NULL, level SMALLINT NOT NULL, level_name VARCHAR(50) NOT NULL, extra JSON NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_F08FC65CD17F50A6 (uuid), INDEX IDX_F08FC65CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE maintenances (active TINYINT DEFAULT 0 NOT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, UNIQUE INDEX UNIQ_C2F7112FD17F50A6 (uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notifications (title VARCHAR(255) DEFAULT NULL, message VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT \'infos\' NOT NULL, access VARCHAR(255) DEFAULT \'public\' NOT NULL, action VARCHAR(255) DEFAULT \'add\' NOT NULL, context JSON DEFAULT NULL, read_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_6000B0D3D17F50A6 (uuid), INDEX IDX_6000B0D3A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reglages (name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, value LONGTEXT DEFAULT NULL, type LONGTEXT NOT NULL, id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_46E7DCF5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reset_password (last_reset_password DATETIME DEFAULT NULL, last_change_password DATETIME DEFAULT NULL, selector VARCHAR(32) NOT NULL, token_hash VARCHAR(64) NOT NULL, password_reset_requested_at DATETIME DEFAULT NULL, password_reset_expires_at DATETIME DEFAULT NULL, consumed_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_B9983CE59692E25D (selector), UNIQUE INDEX UNIQ_B9983CE5A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE role_permission_configurations (permissions JSON NOT NULL, role VARCHAR(50) NOT NULL, id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_4E43BE0D57698A6A (role), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (name VARCHAR(255) NOT NULL, account_must_be_verifed_before DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, enabled TINYINT DEFAULT 0 NOT NULL, security_version INT DEFAULT 1 NOT NULL, UNIQUE INDEX UNIQ_8D93D649D17F50A6 (uuid), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE rememberme_token (series VARCHAR(88) NOT NULL, value VARCHAR(88) NOT NULL, lastUsed DATETIME NOT NULL, class VARCHAR(100) DEFAULT \'\' NOT NULL, username VARCHAR(200) NOT NULL, PRIMARY KEY (series))');
        $this->addSql('ALTER TABLE account_activations ADD CONSTRAINT FK_434BFF7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE logs ADD CONSTRAINT FK_F08FC65CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password ADD CONSTRAINT FK_B9983CE5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account_activations DROP FOREIGN KEY FK_434BFF7A76ED395');
        $this->addSql('ALTER TABLE logs DROP FOREIGN KEY FK_F08FC65CA76ED395');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3A76ED395');
        $this->addSql('ALTER TABLE reset_password DROP FOREIGN KEY FK_B9983CE5A76ED395');
        $this->addSql('DROP TABLE account_activations');
        $this->addSql('DROP TABLE auth_log');
        $this->addSql('DROP TABLE currencies');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE logs');
        $this->addSql('DROP TABLE maintenances');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE reglages');
        $this->addSql('DROP TABLE reset_password');
        $this->addSql('DROP TABLE role_permission_configurations');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE rememberme_token');
    }
}
