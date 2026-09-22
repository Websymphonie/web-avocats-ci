<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les membres structurés du Conseil de l’Ordre.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE council_member (full_name VARCHAR(255) NOT NULL, `function` VARCHAR(255) NOT NULL, portrait_media_id INT DEFAULT NULL, sort_order INT DEFAULT 0 NOT NULL, mandate_started_at DATETIME DEFAULT NULL, mandate_ended_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_EF206879D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE council_member');
    }
}
