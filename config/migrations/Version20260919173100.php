<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919173100 extends AbstractMigration
{
    public function getDescription(): string { return 'Aligne les index et la table de tags des publications documentaires sur Doctrine.'; }
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stored_file RENAME INDEX uniq_stored_file_storage_name TO UNIQ_C339E77C570EB513');
        $this->addSql('ALTER TABLE stored_file RENAME INDEX uniq_stored_file_uuid TO UNIQ_C339E77CD17F50A6');
        $this->addSql('DROP INDEX IDX_DOCUMENT_PUBLICATION_FILE ON document_publication');
        $this->addSql('ALTER TABLE document_publication RENAME INDEX uniq_document_publication_slug TO UNIQ_2D955013989D9B62');
        $this->addSql('ALTER TABLE document_publication RENAME INDEX uniq_document_publication_uuid TO UNIQ_2D955013D17F50A6');
        $this->addSql('ALTER TABLE document_publication_tag DROP FOREIGN KEY FK_DOCUMENT_TAG_DOCUMENT');
        $this->addSql('DROP INDEX IDX_DOCUMENT_TAG_DOCUMENT ON document_publication_tag');
        $this->addSql('ALTER TABLE document_publication_tag CHANGE document_publication_id document_publication_entity_id INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (document_publication_entity_id, tag_entity_id)');
        $this->addSql('ALTER TABLE document_publication_tag ADD CONSTRAINT FK_291B5A92BB784514 FOREIGN KEY (document_publication_entity_id) REFERENCES document_publication (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_291B5A92BB784514 ON document_publication_tag (document_publication_entity_id)');
        $this->addSql('ALTER TABLE document_publication_tag RENAME INDEX idx_document_tag_tag TO IDX_291B5A929012477A');
    }
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document_publication_tag DROP FOREIGN KEY FK_291B5A92BB784514');
        $this->addSql('DROP INDEX IDX_291B5A929012477A ON document_publication_tag');
        $this->addSql('DROP INDEX IDX_291B5A92BB784514 ON document_publication_tag');
        $this->addSql('ALTER TABLE document_publication_tag CHANGE document_publication_entity_id document_publication_id INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (document_publication_id, tag_entity_id)');
        $this->addSql('CREATE INDEX IDX_DOCUMENT_TAG_DOCUMENT ON document_publication_tag (document_publication_id)');
        $this->addSql('ALTER TABLE document_publication_tag ADD CONSTRAINT FK_DOCUMENT_TAG_DOCUMENT FOREIGN KEY (document_publication_id) REFERENCES document_publication (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_publication RENAME INDEX UNIQ_2D955013989D9B62 TO uniq_document_publication_slug');
        $this->addSql('ALTER TABLE document_publication RENAME INDEX UNIQ_2D955013D17F50A6 TO uniq_document_publication_uuid');
        $this->addSql('CREATE INDEX IDX_DOCUMENT_PUBLICATION_FILE ON document_publication (stored_file_id)');
        $this->addSql('ALTER TABLE stored_file RENAME INDEX UNIQ_C339E77C570EB513 TO uniq_stored_file_storage_name');
        $this->addSql('ALTER TABLE stored_file RENAME INDEX UNIQ_C339E77CD17F50A6 TO uniq_stored_file_uuid');
    }
}
