<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les mandats structurés du Bâtonnier.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE batonnier_mandate (full_name VARCHAR(255) NOT NULL, portrait_media_id INT DEFAULT NULL, mandate_started_at DATETIME NOT NULL, mandate_ended_at DATETIME DEFAULT NULL, summary LONGTEXT DEFAULT NULL, current_marker INT DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_50157AAAD17F50A6 (uuid), UNIQUE INDEX UNIQ_BATONNIER_CURRENT_MARKER (current_marker), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE batonnier_mandate');
    }
}
