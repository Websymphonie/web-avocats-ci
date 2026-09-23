<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260923100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Prépare la publication des profils Avocat et Cabinet dans le futur annuaire.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lawyer_profile ADD uuid BINARY(16) DEFAULT NULL, ADD directory_visible TINYINT(1) DEFAULT 0 NOT NULL, ADD professional_email VARCHAR(255) DEFAULT NULL, ADD professional_phone VARCHAR(80) DEFAULT NULL, ADD portrait_media_id INT DEFAULT NULL');
        $this->addSql('UPDATE lawyer_profile SET uuid = UUID_TO_BIN(UUID()) WHERE uuid IS NULL');
        $this->addSql('ALTER TABLE lawyer_profile MODIFY uuid BINARY(16) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7C3D0B5DD17F50A6 ON lawyer_profile (uuid)');
        $this->addSql('CREATE INDEX idx_lawyer_directory_visible ON lawyer_profile (directory_visible)');
        $this->addSql('ALTER TABLE cabinet ADD directory_visible TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX idx_cabinet_directory_visible ON cabinet (directory_visible)');
        $this->addSql('DROP INDEX IDX_4B3A4FADBF396750 ON cabinet');
        $this->addSql("ALTER TABLE cabinet MODIFY country VARCHAR(120) DEFAULT 'Côte d’Ivoire' NOT NULL, MODIFY status VARCHAR(20) DEFAULT 'ACTIVE' NOT NULL");
        $this->addSql('ALTER TABLE cabinet RENAME INDEX UNIQ_4B3A4FADD17F50A6 TO UNIQ_4CED05B0D17F50A6');
        $this->addSql("ALTER TABLE lawyer_profile MODIFY professional_status VARCHAR(40) DEFAULT 'ACTIVE' NOT NULL");
        $this->addSql('ALTER TABLE lawyer_profile RENAME INDEX UNIQ_7C3D0B5DD17F50A6 TO UNIQ_8EDD069D17F50A6');
        $this->addSql('ALTER TABLE lawyer_profile RENAME INDEX UNIQ_7C3D0B5DA76ED395 TO UNIQ_8EDD069A76ED395');
        $this->addSql('ALTER TABLE lawyer_profile RENAME INDEX IDX_7C3D0B5D1C5D7F4A TO IDX_8EDD069D351EC');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_lawyer_directory_visible ON lawyer_profile');
        $this->addSql('DROP INDEX UNIQ_7C3D0B5DD17F50A6 ON lawyer_profile');
        $this->addSql('ALTER TABLE lawyer_profile DROP uuid, DROP directory_visible, DROP professional_email, DROP professional_phone, DROP portrait_media_id');
        $this->addSql('DROP INDEX idx_cabinet_directory_visible ON cabinet');
        $this->addSql('ALTER TABLE cabinet DROP directory_visible');
    }
}
