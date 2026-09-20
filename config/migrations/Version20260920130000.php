<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260920130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la fondation append-only du business audit trail.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE audit_entry (context VARCHAR(50) NOT NULL, action VARCHAR(150) NOT NULL, actor_type VARCHAR(20) NOT NULL, actor_id VARCHAR(191) DEFAULT NULL, target_type VARCHAR(100) DEFAULT NULL, target_id VARCHAR(191) DEFAULT NULL, metadata JSON NOT NULL, deduplication_key VARCHAR(64) DEFAULT NULL, occurred_at DATETIME NOT NULL, created_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, UNIQUE INDEX UNIQ_2C6AF987EE427CD1 (deduplication_key), UNIQUE INDEX UNIQ_2C6AF987D17F50A6 (uuid), INDEX idx_audit_entry_occurred_at (occurred_at), INDEX idx_audit_entry_context (context), INDEX idx_audit_entry_action (action), INDEX idx_audit_entry_actor_id (actor_id), INDEX idx_audit_entry_target (target_type, target_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE audit_entry');
    }
}
