<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919161000 extends AbstractMigration
{
    public function getDescription(): string { return 'Aligne les noms d’index des tables de vidéos éditoriales sur Doctrine.'; }
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE editorial_video_entity RENAME INDEX uniq_editorial_video_slug TO UNIQ_ED04DBDF989D9B62');
        $this->addSql('ALTER TABLE editorial_video_entity RENAME INDEX uniq_editorial_video_uuid TO UNIQ_ED04DBDFD17F50A6');
        $this->addSql('ALTER TABLE editorial_video_tag RENAME INDEX idx_editorial_video_video TO IDX_508753A685356F1D');
        $this->addSql('ALTER TABLE editorial_video_tag RENAME INDEX idx_editorial_video_tag TO IDX_508753A69012477A');
    }
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE editorial_video_entity RENAME INDEX UNIQ_ED04DBDF989D9B62 TO uniq_editorial_video_slug');
        $this->addSql('ALTER TABLE editorial_video_entity RENAME INDEX UNIQ_ED04DBDFD17F50A6 TO uniq_editorial_video_uuid');
        $this->addSql('ALTER TABLE editorial_video_tag RENAME INDEX IDX_508753A685356F1D TO idx_editorial_video_video');
        $this->addSql('ALTER TABLE editorial_video_tag RENAME INDEX IDX_508753A69012477A TO idx_editorial_video_tag');
    }
}
