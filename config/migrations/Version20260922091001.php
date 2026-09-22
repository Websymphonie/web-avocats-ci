<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922091001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronise les index de la taxonomie des vidéos avec le mapping Doctrine.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE editorial_video_entity RENAME INDEX idx_editorial_video_category TO IDX_ED04DBDF12469DE2');
        $this->addSql('ALTER TABLE editorial_video_category RENAME INDEX uniq_editorial_video_category_slug TO UNIQ_CD784C47989D9B62');
        $this->addSql('ALTER TABLE editorial_video_category RENAME INDEX uniq_editorial_video_category_uuid TO UNIQ_CD784C47D17F50A6');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE editorial_video_entity RENAME INDEX IDX_ED04DBDF12469DE2 TO idx_editorial_video_category');
        $this->addSql('ALTER TABLE editorial_video_category RENAME INDEX UNIQ_CD784C47989D9B62 TO uniq_editorial_video_category_slug');
        $this->addSql('ALTER TABLE editorial_video_category RENAME INDEX UNIQ_CD784C47D17F50A6 TO uniq_editorial_video_category_uuid');
    }
}
