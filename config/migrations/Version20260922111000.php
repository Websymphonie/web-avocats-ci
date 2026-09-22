<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922111000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aligne le schéma du message de contact sur le mapping UUID Doctrine.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact_message CHANGE uuid uuid BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_message RENAME INDEX UNIQ_CONTACT_MESSAGE_UUID TO UNIQ_2C9211FED17F50A6');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact_message RENAME INDEX UNIQ_2C9211FED17F50A6 TO UNIQ_CONTACT_MESSAGE_UUID');
    }
}
