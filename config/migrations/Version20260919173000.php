<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919173000 extends AbstractMigration
{
    public function getDescription(): string { return 'Ajoute les publications de documents et leur stockage privé minimal.'; }
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE stored_file (
              original_name VARCHAR(255) NOT NULL,
              storage_name VARCHAR(255) NOT NULL,
              mime_type VARCHAR(128) NOT NULL,
              size INT NOT NULL,
              checksum VARCHAR(64) NOT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_STORED_FILE_STORAGE_NAME (storage_name),
              UNIQUE INDEX UNIQ_STORED_FILE_UUID (uuid),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE document_publication (
              title VARCHAR(255) NOT NULL,
              slug VARCHAR(255) NOT NULL,
              description LONGTEXT NOT NULL,
              stored_file_id INT NOT NULL,
              access_level VARCHAR(255) NOT NULL,
              status VARCHAR(255) NOT NULL,
              published_at DATETIME DEFAULT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_DOCUMENT_PUBLICATION_SLUG (slug),
              UNIQUE INDEX UNIQ_DOCUMENT_PUBLICATION_UUID (uuid),
              INDEX IDX_DOCUMENT_PUBLICATION_FILE (stored_file_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE document_publication_tag (
              document_publication_id INT NOT NULL,
              tag_entity_id INT NOT NULL,
              INDEX IDX_DOCUMENT_TAG_DOCUMENT (document_publication_id),
              INDEX IDX_DOCUMENT_TAG_TAG (tag_entity_id),
              PRIMARY KEY (document_publication_id, tag_entity_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql('ALTER TABLE document_publication_tag ADD CONSTRAINT FK_DOCUMENT_TAG_DOCUMENT FOREIGN KEY (document_publication_id) REFERENCES document_publication (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_publication_tag ADD CONSTRAINT FK_DOCUMENT_TAG_TAG FOREIGN KEY (tag_entity_id) REFERENCES tag (id) ON DELETE CASCADE');
    }
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document_publication_tag DROP FOREIGN KEY FK_DOCUMENT_TAG_DOCUMENT');
        $this->addSql('ALTER TABLE document_publication_tag DROP FOREIGN KEY FK_DOCUMENT_TAG_TAG');
        $this->addSql('DROP TABLE document_publication_tag');
        $this->addSql('DROP TABLE document_publication');
        $this->addSql('DROP TABLE stored_file');
    }
}
