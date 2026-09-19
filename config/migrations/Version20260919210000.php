<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919210000 extends AbstractMigration
{
    public function getDescription(): string { return 'Ajoute le contenu pédagogique et les ressources privées des leçons.'; }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE lesson ADD content LONGTEXT DEFAULT NULL, ADD video_provider VARCHAR(20) DEFAULT NULL, ADD video_url VARCHAR(2048) DEFAULT NULL, ADD external_video_id VARCHAR(64) DEFAULT NULL");
        $this->addSql(<<<'SQL'
            CREATE TABLE lesson_resource (
              lesson_id INT NOT NULL,
              stored_file_id INT NOT NULL,
              title VARCHAR(255) NOT NULL,
              position INT NOT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_F4D6BE0FD17F50A6 (uuid),
              INDEX IDX_F4D6BE0FCDF80196 (lesson_id),
              INDEX IDX_F4D6BE0F7590B9E4 (stored_file_id),
              UNIQUE INDEX uniq_lesson_resource_lesson_position (lesson_id, position),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lesson_resource');
        $this->addSql('ALTER TABLE lesson DROP content, DROP video_provider, DROP video_url, DROP external_video_id');
    }
}
