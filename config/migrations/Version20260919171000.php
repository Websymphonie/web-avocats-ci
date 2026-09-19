<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919171000 extends AbstractMigration
{
    public function getDescription(): string { return 'Aligne les noms d’index et la table de tags des galeries avec Doctrine.'; }
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media RENAME INDEX UNIQ_MEDIA_STORAGE_NAME TO UNIQ_6A2CA10C570EB513');
        $this->addSql('ALTER TABLE media RENAME INDEX UNIQ_MEDIA_STORAGE_PATH TO UNIQ_6A2CA10C279401A');
        $this->addSql('ALTER TABLE media RENAME INDEX UNIQ_MEDIA_UUID TO UNIQ_6A2CA10CD17F50A6');
        $this->addSql('ALTER TABLE photo_gallery_item RENAME INDEX IDX_GALLERY_ITEM_GALLERY TO IDX_36F04814E7AF8F');
        $this->addSql('ALTER TABLE photo_gallery RENAME INDEX UNIQ_PHOTO_GALLERY_SLUG TO UNIQ_72CB6FB7989D9B62');
        $this->addSql('ALTER TABLE photo_gallery RENAME INDEX UNIQ_PHOTO_GALLERY_UUID TO UNIQ_72CB6FB7D17F50A6');
        $this->addSql('ALTER TABLE photo_gallery_tag DROP FOREIGN KEY FK_GALLERY_TAG_GALLERY');
        $this->addSql('ALTER TABLE photo_gallery_tag CHANGE photo_gallery_id photo_gallery_entity_id INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (photo_gallery_entity_id, tag_entity_id)');
        $this->addSql('ALTER TABLE photo_gallery_tag ADD CONSTRAINT FK_1BC94AD27F729816 FOREIGN KEY (photo_gallery_entity_id) REFERENCES photo_gallery (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE photo_gallery_tag RENAME INDEX IDX_GALLERY_TAG_GALLERY TO IDX_1BC94AD27F729816');
        $this->addSql('ALTER TABLE photo_gallery_tag RENAME INDEX IDX_GALLERY_TAG_TAG TO IDX_1BC94AD29012477A');
    }
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photo_gallery_tag DROP FOREIGN KEY FK_1BC94AD27F729816');
        $this->addSql('ALTER TABLE photo_gallery_tag RENAME INDEX IDX_1BC94AD27F729816 TO IDX_GALLERY_TAG_GALLERY');
        $this->addSql('ALTER TABLE photo_gallery_tag RENAME INDEX IDX_1BC94AD29012477A TO IDX_GALLERY_TAG_TAG');
        $this->addSql('ALTER TABLE photo_gallery_tag CHANGE photo_gallery_entity_id photo_gallery_id INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (photo_gallery_id, tag_entity_id)');
        $this->addSql('ALTER TABLE photo_gallery_tag ADD CONSTRAINT FK_GALLERY_TAG_GALLERY FOREIGN KEY (photo_gallery_id) REFERENCES photo_gallery (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE photo_gallery RENAME INDEX UNIQ_72CB6FB7989D9B62 TO UNIQ_PHOTO_GALLERY_SLUG');
        $this->addSql('ALTER TABLE photo_gallery RENAME INDEX UNIQ_72CB6FB7D17F50A6 TO UNIQ_PHOTO_GALLERY_UUID');
        $this->addSql('ALTER TABLE photo_gallery_item RENAME INDEX IDX_36F04814E7AF8F TO IDX_GALLERY_ITEM_GALLERY');
        $this->addSql('ALTER TABLE media RENAME INDEX UNIQ_6A2CA10C570EB513 TO UNIQ_MEDIA_STORAGE_NAME');
        $this->addSql('ALTER TABLE media RENAME INDEX UNIQ_6A2CA10C279401A TO UNIQ_MEDIA_STORAGE_PATH');
        $this->addSql('ALTER TABLE media RENAME INDEX UNIQ_6A2CA10CD17F50A6 TO UNIQ_MEDIA_UUID');
    }
}
