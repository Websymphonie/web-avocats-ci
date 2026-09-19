<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919170000 extends AbstractMigration
{
    public function getDescription(): string { return 'Ajoute les galeries photos et le stockage minimal des images publiques.'; }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE media (
              original_name VARCHAR(255) NOT NULL,
              storage_name VARCHAR(128) NOT NULL,
              mime_type VARCHAR(64) NOT NULL,
              size INT NOT NULL,
              width INT NOT NULL,
              height INT NOT NULL,
              storage_path VARCHAR(512) NOT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_MEDIA_STORAGE_NAME (storage_name),
              UNIQUE INDEX UNIQ_MEDIA_STORAGE_PATH (storage_path),
              UNIQUE INDEX UNIQ_MEDIA_UUID (uuid),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE photo_gallery (
              title VARCHAR(255) NOT NULL,
              slug VARCHAR(255) NOT NULL,
              description LONGTEXT NOT NULL,
              status VARCHAR(255) NOT NULL,
              published_at DATETIME DEFAULT NULL,
              cover_media_id INT DEFAULT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_PHOTO_GALLERY_SLUG (slug),
              UNIQUE INDEX UNIQ_PHOTO_GALLERY_UUID (uuid),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE photo_gallery_item (
              gallery_id INT NOT NULL,
              media_id INT NOT NULL,
              position INT NOT NULL,
              alt_text VARCHAR(500) NOT NULL,
              caption LONGTEXT DEFAULT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              INDEX IDX_GALLERY_ITEM_GALLERY (gallery_id),
              UNIQUE INDEX uniq_gallery_media (gallery_id, media_id),
              UNIQUE INDEX uniq_gallery_position (gallery_id, position),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE photo_gallery_tag (
              photo_gallery_id INT NOT NULL,
              tag_entity_id INT NOT NULL,
              INDEX IDX_GALLERY_TAG_GALLERY (photo_gallery_id),
              INDEX IDX_GALLERY_TAG_TAG (tag_entity_id),
              PRIMARY KEY (photo_gallery_id, tag_entity_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql('ALTER TABLE photo_gallery_item ADD CONSTRAINT FK_GALLERY_ITEM_GALLERY FOREIGN KEY (gallery_id) REFERENCES photo_gallery (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE photo_gallery_tag ADD CONSTRAINT FK_GALLERY_TAG_GALLERY FOREIGN KEY (photo_gallery_id) REFERENCES photo_gallery (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE photo_gallery_tag ADD CONSTRAINT FK_GALLERY_TAG_TAG FOREIGN KEY (tag_entity_id) REFERENCES tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photo_gallery_item DROP FOREIGN KEY FK_GALLERY_ITEM_GALLERY');
        $this->addSql('ALTER TABLE photo_gallery_tag DROP FOREIGN KEY FK_GALLERY_TAG_GALLERY');
        $this->addSql('ALTER TABLE photo_gallery_tag DROP FOREIGN KEY FK_GALLERY_TAG_TAG');
        $this->addSql('DROP TABLE photo_gallery_item');
        $this->addSql('DROP TABLE photo_gallery_tag');
        $this->addSql('DROP TABLE photo_gallery');
        $this->addSql('DROP TABLE media');
    }
}
