<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260921100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la référence YouTube facultative aux sessions LIVE.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE live_training_details ADD stream_provider VARCHAR(255) DEFAULT NULL, ADD external_stream_id VARCHAR(128) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE live_training_details DROP stream_provider, DROP external_stream_id');
    }
}
