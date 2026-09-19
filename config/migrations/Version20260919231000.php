<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919231000 extends AbstractMigration
{
    public function getDescription(): string { return 'Aligne les contraintes Doctrine des taxonomies Learning.'; }
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE training_category RENAME INDEX UNIQ_TRAINING_CATEGORY_SLUG TO UNIQ_E1290A56989D9B62');
        $this->addSql('ALTER TABLE training_category RENAME INDEX UNIQ_TRAINING_CATEGORY_UUID TO UNIQ_E1290A56D17F50A6');
        $this->addSql('ALTER TABLE training_training_category ADD CONSTRAINT FK_6F265B11EFA91EA9 FOREIGN KEY (training_entity_id) REFERENCES training (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE training_training_category ADD CONSTRAINT FK_6F265B11AE73740F FOREIGN KEY (training_category_entity_id) REFERENCES training_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE training_training_category RENAME INDEX IDX_TRAINING_CATEGORY_TRAINING TO IDX_6F265B11EFA91EA9');
        $this->addSql('ALTER TABLE training_training_category RENAME INDEX IDX_TRAINING_CATEGORY_CATEGORY TO IDX_6F265B11AE73740F');
        $this->addSql('ALTER TABLE training_training_tag ADD CONSTRAINT FK_8124ECC7EFA91EA9 FOREIGN KEY (training_entity_id) REFERENCES training (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE training_training_tag ADD CONSTRAINT FK_8124ECC7EEBB28EB FOREIGN KEY (training_tag_entity_id) REFERENCES training_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE training_training_tag RENAME INDEX IDX_TRAINING_TAG_TRAINING TO IDX_8124ECC7EFA91EA9');
        $this->addSql('ALTER TABLE training_training_tag RENAME INDEX IDX_TRAINING_TAG_TAG TO IDX_8124ECC7EEBB28EB');
        $this->addSql('ALTER TABLE training_tag RENAME INDEX UNIQ_TRAINING_TAG_SLUG TO UNIQ_9937C0CA989D9B62');
        $this->addSql('ALTER TABLE training_tag RENAME INDEX UNIQ_TRAINING_TAG_UUID TO UNIQ_9937C0CAD17F50A6');
    }
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE training_training_category DROP FOREIGN KEY FK_6F265B11EFA91EA9');
        $this->addSql('ALTER TABLE training_training_category DROP FOREIGN KEY FK_6F265B11AE73740F');
        $this->addSql('ALTER TABLE training_training_tag DROP FOREIGN KEY FK_8124ECC7EFA91EA9');
        $this->addSql('ALTER TABLE training_training_tag DROP FOREIGN KEY FK_8124ECC7EEBB28EB');
        $this->addSql('ALTER TABLE training_category RENAME INDEX UNIQ_E1290A56989D9B62 TO UNIQ_TRAINING_CATEGORY_SLUG');
        $this->addSql('ALTER TABLE training_category RENAME INDEX UNIQ_E1290A56D17F50A6 TO UNIQ_TRAINING_CATEGORY_UUID');
        $this->addSql('ALTER TABLE training_training_category RENAME INDEX IDX_6F265B11EFA91EA9 TO IDX_TRAINING_CATEGORY_TRAINING');
        $this->addSql('ALTER TABLE training_training_category RENAME INDEX IDX_6F265B11AE73740F TO IDX_TRAINING_CATEGORY_CATEGORY');
        $this->addSql('ALTER TABLE training_training_tag RENAME INDEX IDX_8124ECC7EFA91EA9 TO IDX_TRAINING_TAG_TRAINING');
        $this->addSql('ALTER TABLE training_training_tag RENAME INDEX IDX_8124ECC7EEBB28EB TO IDX_TRAINING_TAG_TAG');
        $this->addSql('ALTER TABLE training_tag RENAME INDEX UNIQ_9937C0CA989D9B62 TO UNIQ_TRAINING_TAG_SLUG');
        $this->addSql('ALTER TABLE training_tag RENAME INDEX UNIQ_9937C0CAD17F50A6 TO UNIQ_TRAINING_TAG_UUID');
    }
}
