<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute l’archivage des messages du formulaire public de contact.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE contact_message (full_name VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(80) DEFAULT NULL, subject VARCHAR(180) NOT NULL, message LONGTEXT NOT NULL, consent_at DATETIME NOT NULL, submitted_at DATETIME NOT NULL, delivery_status VARCHAR(20) NOT NULL, sent_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL COMMENT '(DC2Type:uuid)', created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_CONTACT_MESSAGE_UUID (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_message');
    }
}
