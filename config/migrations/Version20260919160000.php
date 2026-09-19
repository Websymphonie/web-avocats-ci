<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919160000 extends AbstractMigration
{
    public function getDescription(): string { return 'Ajoute la gestion backoffice des vidéos éditoriales et leur association aux tags.'; }
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE editorial_video_entity (
              title VARCHAR(255) NOT NULL,
              slug VARCHAR(255) NOT NULL,
              excerpt LONGTEXT DEFAULT NULL,
              description LONGTEXT NOT NULL,
              provider VARCHAR(255) NOT NULL,
              video_url VARCHAR(2048) NOT NULL,
              external_video_id VARCHAR(255) DEFAULT NULL,
              status VARCHAR(255) NOT NULL,
              published_at DATETIME DEFAULT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_EDITORIAL_VIDEO_SLUG (slug),
              UNIQUE INDEX UNIQ_EDITORIAL_VIDEO_UUID (uuid),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE editorial_video_tag (
              editorial_video_entity_id INT NOT NULL,
              tag_entity_id INT NOT NULL,
              INDEX IDX_EDITORIAL_VIDEO_VIDEO (editorial_video_entity_id),
              INDEX IDX_EDITORIAL_VIDEO_TAG (tag_entity_id),
              PRIMARY KEY (editorial_video_entity_id, tag_entity_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql('ALTER TABLE editorial_video_tag ADD CONSTRAINT FK_EDITORIAL_VIDEO_VIDEO FOREIGN KEY (editorial_video_entity_id) REFERENCES editorial_video_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE editorial_video_tag ADD CONSTRAINT FK_EDITORIAL_VIDEO_TAG FOREIGN KEY (tag_entity_id) REFERENCES tag (id) ON DELETE CASCADE');
    }
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE editorial_video_tag DROP FOREIGN KEY FK_EDITORIAL_VIDEO_VIDEO');
        $this->addSql('ALTER TABLE editorial_video_tag DROP FOREIGN KEY FK_EDITORIAL_VIDEO_TAG');
        $this->addSql('DROP TABLE editorial_video_tag');
        $this->addSql('DROP TABLE editorial_video_entity');
    }
}
