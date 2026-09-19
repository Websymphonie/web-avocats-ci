<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919190000 extends AbstractMigration
{
    public function getDescription(): string { return 'Ajoute la fondation Learning et le catalogue des formations COURSE.'; }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE training (
              type VARCHAR(255) NOT NULL,
              title VARCHAR(255) NOT NULL,
              slug VARCHAR(255) NOT NULL,
              summary LONGTEXT NOT NULL,
              description LONGTEXT NOT NULL,
              visibility VARCHAR(255) NOT NULL,
              access_type VARCHAR(255) NOT NULL,
              status VARCHAR(255) NOT NULL,
              published_at DATETIME DEFAULT NULL,
              cover_media_id INT DEFAULT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_D5128A8F989D9B62 (slug),
              UNIQUE INDEX UNIQ_D5128A8FD17F50A6 (uuid),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE training');
    }
}
