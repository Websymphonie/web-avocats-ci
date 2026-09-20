<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260920151000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aligne les index Doctrine de la table des pages statiques.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page RENAME INDEX uniq_page_slug TO UNIQ_140AB620989D9B62');
        $this->addSql('ALTER TABLE page RENAME INDEX uniq_page_uuid TO UNIQ_140AB620D17F50A6');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page RENAME INDEX UNIQ_140AB620989D9B62 TO uniq_page_slug');
        $this->addSql('ALTER TABLE page RENAME INDEX UNIQ_140AB620D17F50A6 TO uniq_page_uuid');
    }
}
