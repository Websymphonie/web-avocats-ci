<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919232000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les détails des formations LIVE Learning.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE live_training_details (
              training_id INT NOT NULL,
              starts_at DATETIME NOT NULL,
              ends_at DATETIME NOT NULL,
              delivery_mode VARCHAR(255) NOT NULL,
              location VARCHAR(500) DEFAULT NULL,
              join_url VARCHAR(2048) DEFAULT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_EB8BA199D17F50A6 (uuid),
              UNIQUE INDEX uniq_live_training_details_training (training_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE live_training_details');
    }
}
