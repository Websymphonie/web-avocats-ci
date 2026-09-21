<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260921110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le groupe éditorial facultatif aux pages statiques.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD editorial_group VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page DROP editorial_group');
    }
}
