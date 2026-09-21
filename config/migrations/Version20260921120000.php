<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260921120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute l’ordre éditorial facultatif des pages statiques.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD sort_order INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page DROP sort_order');
    }
}
