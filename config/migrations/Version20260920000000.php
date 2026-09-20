<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260920000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add training offers and payment foundation with fake provider support.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE training_offer (training_id INT NOT NULL, amount INT NOT NULL, currency VARCHAR(3) NOT NULL, active TINYINT NOT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_C60C0D53D17F50A6 (uuid), INDEX IDX_C60C0D534B1EFC02 (active), UNIQUE INDEX uniq_training_offer_training (training_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (user_id INT NOT NULL, training_id INT NOT NULL, training_offer_id INT DEFAULT NULL, amount INT NOT NULL, currency VARCHAR(3) NOT NULL, status VARCHAR(20) NOT NULL, provider VARCHAR(20) NOT NULL, provider_reference VARCHAR(255) DEFAULT NULL, idempotency_key VARCHAR(128) NOT NULL, confirmed_at DATETIME DEFAULT NULL, failed_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_6D28840DD17F50A6 (uuid), INDEX IDX_6D28840DB4A36779 (training_offer_id), INDEX IDX_6D28840DA76ED395 (user_id), INDEX IDX_6D28840DBEFD98D1 (training_id), INDEX IDX_6D28840D7B00651C (status), INDEX IDX_6D28840D8B8E8428 (created_at), UNIQUE INDEX uniq_payment_user_idempotency (user_id, idempotency_key), UNIQUE INDEX uniq_payment_provider_reference (provider, provider_reference), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DB4A36779 FOREIGN KEY (training_offer_id) REFERENCES training_offer (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DB4A36779');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE training_offer');
    }
}
