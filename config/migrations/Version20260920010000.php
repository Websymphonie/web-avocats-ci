<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260920010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute une clé de déduplication aux notifications transactionnelles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notifications ADD deduplication_key VARCHAR(64) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6000B0D3EE427CD1 ON notifications (deduplication_key)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_6000B0D3EE427CD1 ON notifications');
        $this->addSql('ALTER TABLE notifications DROP deduplication_key');
    }
}
