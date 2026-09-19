<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919230000 extends AbstractMigration
{
    public function getDescription(): string { return 'Ajoute les catégories et tags Learning des formations.'; }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE training_category (name VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_TRAINING_CATEGORY_SLUG (slug), UNIQUE INDEX UNIQ_TRAINING_CATEGORY_UUID (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`");
        $this->addSql("CREATE TABLE training_tag (name VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_TRAINING_TAG_SLUG (slug), UNIQUE INDEX UNIQ_TRAINING_TAG_UUID (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`");
        $this->addSql("CREATE TABLE training_training_category (training_entity_id INT NOT NULL, training_category_entity_id INT NOT NULL, INDEX IDX_TRAINING_CATEGORY_TRAINING (training_entity_id), INDEX IDX_TRAINING_CATEGORY_CATEGORY (training_category_entity_id), PRIMARY KEY(training_entity_id, training_category_entity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`");
        $this->addSql("CREATE TABLE training_training_tag (training_entity_id INT NOT NULL, training_tag_entity_id INT NOT NULL, INDEX IDX_TRAINING_TAG_TRAINING (training_entity_id), INDEX IDX_TRAINING_TAG_TAG (training_tag_entity_id), PRIMARY KEY(training_entity_id, training_tag_entity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE training_training_category');
        $this->addSql('DROP TABLE training_training_tag');
        $this->addSql('DROP TABLE training_category');
        $this->addSql('DROP TABLE training_tag');
    }
}
