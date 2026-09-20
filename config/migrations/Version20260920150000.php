<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260920150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la gestion des pages statiques du ContentContext.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE page (
              title VARCHAR(255) NOT NULL,
              slug VARCHAR(255) NOT NULL,
              content LONGTEXT NOT NULL,
              status VARCHAR(255) NOT NULL,
              published_at DATETIME DEFAULT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_PAGE_SLUG (slug),
              UNIQUE INDEX UNIQ_PAGE_UUID (uuid),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE page');
    }
}
