<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919180000 extends AbstractMigration
{
    public function getDescription(): string { return 'Ajoute les couvertures et galeries optionnelles aux actualités et événements.'; }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE news_entity ADD cover_media_id INT DEFAULT NULL, ADD photo_gallery_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_entity ADD cover_media_id INT DEFAULT NULL, ADD photo_gallery_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE news_entity DROP cover_media_id, DROP photo_gallery_id');
        $this->addSql('ALTER TABLE event_entity DROP cover_media_id, DROP photo_gallery_id');
    }
}
