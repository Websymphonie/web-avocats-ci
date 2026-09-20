<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260920140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le suivi durable du fulfillment des paiements confirmés.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payment ADD fulfillment_status VARCHAR(20) DEFAULT NULL, ADD fulfillment_completed_at DATETIME DEFAULT NULL, ADD fulfillment_attempts INT NOT NULL DEFAULT 0, ADD last_fulfillment_attempt_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_6D28840D4AD8F6E2 ON payment (fulfillment_status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_6D28840D4AD8F6E2 ON payment');
        $this->addSql('ALTER TABLE payment DROP fulfillment_status, DROP fulfillment_completed_at, DROP fulfillment_attempts, DROP last_fulfillment_attempt_at');
    }
}
