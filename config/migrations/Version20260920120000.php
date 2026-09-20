<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260920120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Autorise les adresses IPv6 complètes dans AuthLog.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_log CHANGE user_ip user_ip VARCHAR(45) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_log CHANGE user_ip user_ip VARCHAR(20) DEFAULT NULL');
    }
}
