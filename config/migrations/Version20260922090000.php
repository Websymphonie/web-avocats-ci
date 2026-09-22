<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les catégories éditoriales des vidéos et leur association aux vidéos.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE editorial_video_category (name VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_EDITORIAL_VIDEO_CATEGORY_SLUG (slug), UNIQUE INDEX UNIQ_EDITORIAL_VIDEO_CATEGORY_UUID (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`");
        $this->addSql('ALTER TABLE editorial_video_entity ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE editorial_video_entity ADD CONSTRAINT FK_EDITORIAL_VIDEO_CATEGORY FOREIGN KEY (category_id) REFERENCES editorial_video_category (id) ON DELETE RESTRICT');
        $this->addSql('CREATE INDEX IDX_EDITORIAL_VIDEO_CATEGORY ON editorial_video_entity (category_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE editorial_video_entity DROP FOREIGN KEY FK_EDITORIAL_VIDEO_CATEGORY');
        $this->addSql('DROP INDEX IDX_EDITORIAL_VIDEO_CATEGORY ON editorial_video_entity');
        $this->addSql('ALTER TABLE editorial_video_entity DROP category_id');
        $this->addSql('DROP TABLE editorial_video_category');
    }
}
