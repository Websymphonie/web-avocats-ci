<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la gestion backoffice des événements et leurs catégories.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE event_category (
              name VARCHAR(150) NOT NULL,
              slug VARCHAR(180) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_40A0F011989D9B62 (slug),
              UNIQUE INDEX UNIQ_40A0F011D17F50A6 (uuid),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event_entity (
              title VARCHAR(255) NOT NULL,
              slug VARCHAR(255) NOT NULL,
              excerpt LONGTEXT DEFAULT NULL,
              description LONGTEXT NOT NULL,
              format VARCHAR(255) NOT NULL,
              starts_at DATETIME NOT NULL,
              ends_at DATETIME DEFAULT NULL,
              venue_name VARCHAR(255) DEFAULT NULL,
              address LONGTEXT DEFAULT NULL,
              online_url VARCHAR(2048) DEFAULT NULL,
              status VARCHAR(255) NOT NULL,
              published_at DATETIME DEFAULT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_3CB1333A989D9B62 (slug),
              UNIQUE INDEX UNIQ_3CB1333AD17F50A6 (uuid),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event_event_category (
              event_entity_id INT NOT NULL,
              event_category_entity_id INT NOT NULL,
              INDEX IDX_9FE446627B04360 (event_entity_id),
              INDEX IDX_9FE446627C6567F9 (event_category_entity_id),
              PRIMARY KEY (event_entity_id, event_category_entity_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event_tag (
              event_entity_id INT NOT NULL,
              tag_entity_id INT NOT NULL,
              INDEX IDX_124672507B04360 (event_entity_id),
              INDEX IDX_124672509012477A (tag_entity_id),
              PRIMARY KEY (event_entity_id, tag_entity_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql('ALTER TABLE event_event_category ADD CONSTRAINT FK_EVENT_CATEGORY_EVENT FOREIGN KEY (event_entity_id) REFERENCES event_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_event_category ADD CONSTRAINT FK_EVENT_CATEGORY_CATEGORY FOREIGN KEY (event_category_entity_id) REFERENCES event_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_tag ADD CONSTRAINT FK_EVENT_TAG_EVENT FOREIGN KEY (event_entity_id) REFERENCES event_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_tag ADD CONSTRAINT FK_EVENT_TAG_TAG FOREIGN KEY (tag_entity_id) REFERENCES tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_event_category DROP FOREIGN KEY FK_EVENT_CATEGORY_EVENT');
        $this->addSql('ALTER TABLE event_event_category DROP FOREIGN KEY FK_EVENT_CATEGORY_CATEGORY');
        $this->addSql('ALTER TABLE event_tag DROP FOREIGN KEY FK_EVENT_TAG_EVENT');
        $this->addSql('ALTER TABLE event_tag DROP FOREIGN KEY FK_EVENT_TAG_TAG');
        $this->addSql('DROP TABLE event_event_category');
        $this->addSql('DROP TABLE event_tag');
        $this->addSql('DROP TABLE event_category');
        $this->addSql('DROP TABLE event_entity');
    }
}
