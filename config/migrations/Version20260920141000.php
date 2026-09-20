<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260920141000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aligne le nom de l’index de fulfillment Payment sur Doctrine.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payment RENAME INDEX IDX_6D28840D4AD8F6E2 TO IDX_6D28840D74E1E8CA');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payment RENAME INDEX IDX_6D28840D74E1E8CA TO IDX_6D28840D4AD8F6E2');
    }
}
