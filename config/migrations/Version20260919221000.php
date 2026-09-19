<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919221000 extends AbstractMigration
{
    public function getDescription(): string { return 'Aligne les index et tailles de colonnes des inscriptions sur le mapping Doctrine.'; }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE enrollment CHANGE status status VARCHAR(255) NOT NULL, CHANGE source source VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE enrollment RENAME INDEX UNIQ_ENROLLMENT_UUID TO UNIQ_DBDCD7E1D17F50A6');
        $this->addSql('ALTER TABLE enrollment RENAME INDEX IDX_ENROLLMENT_TRAINING TO IDX_DBDCD7E1BEFD98D1');
        $this->addSql('ALTER TABLE enrollment RENAME INDEX IDX_ENROLLMENT_USER TO IDX_DBDCD7E1A76ED395');
        $this->addSql('ALTER TABLE enrollment RENAME INDEX IDX_ENROLLMENT_STATUS TO IDX_DBDCD7E17B00651C');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE enrollment RENAME INDEX UNIQ_DBDCD7E1D17F50A6 TO UNIQ_ENROLLMENT_UUID');
        $this->addSql('ALTER TABLE enrollment RENAME INDEX IDX_DBDCD7E1BEFD98D1 TO IDX_ENROLLMENT_TRAINING');
        $this->addSql('ALTER TABLE enrollment RENAME INDEX IDX_DBDCD7E1A76ED395 TO IDX_ENROLLMENT_USER');
        $this->addSql('ALTER TABLE enrollment RENAME INDEX IDX_DBDCD7E17B00651C TO IDX_ENROLLMENT_STATUS');
        $this->addSql('ALTER TABLE enrollment CHANGE status status VARCHAR(20) NOT NULL, CHANGE source source VARCHAR(20) NOT NULL');
    }
}
