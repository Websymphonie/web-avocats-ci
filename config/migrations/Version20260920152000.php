<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260920152000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la couverture facultative des pages statiques.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD cover_media_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page DROP cover_media_id');
    }
}
