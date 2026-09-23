<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260923130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename reserved council_member.function column for MySQL compatibility.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE council_member CHANGE `function` member_function VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE council_member CHANGE member_function `function` VARCHAR(255) NOT NULL');
    }
}
