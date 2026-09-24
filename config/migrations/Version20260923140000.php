<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260923140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les groupes et entrées de personnes institutionnelles associés aux Pages.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE page_person_group (title VARCHAR(255) NOT NULL, group_key VARCHAR(100) NOT NULL, sort_order INT DEFAULT 0 NOT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, page_id INT NOT NULL, UNIQUE INDEX UNIQ_7FEAF74FD17F50A6 (uuid), INDEX IDX_7FEAF74FC4663E4 (page_id), UNIQUE INDEX UNIQ_PAGE_PERSON_GROUP_KEY (page_id, group_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_person_entry (entry_key VARCHAR(120) NOT NULL, display_name VARCHAR(255) NOT NULL, role_label VARCHAR(255) DEFAULT NULL, period_label VARCHAR(255) DEFAULT NULL, portrait_media_id INT DEFAULT NULL, link_url VARCHAR(2048) DEFAULT NULL, sort_order INT DEFAULT 0 NOT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, group_id INT NOT NULL, UNIQUE INDEX UNIQ_390B2EFAD17F50A6 (uuid), INDEX IDX_390B2EFAFE54D947 (group_id), UNIQUE INDEX UNIQ_PAGE_PERSON_ENTRY_KEY (group_id, entry_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE page_person_group ADD CONSTRAINT FK_7FEAF74FC4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_person_entry ADD CONSTRAINT FK_390B2EFAFE54D947 FOREIGN KEY (group_id) REFERENCES page_person_group (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page_person_entry DROP FOREIGN KEY FK_390B2EFAFE54D947');
        $this->addSql('ALTER TABLE page_person_group DROP FOREIGN KEY FK_7FEAF74FC4663E4');
        $this->addSql('DROP TABLE page_person_entry');
        $this->addSql('DROP TABLE page_person_group');
    }
}
