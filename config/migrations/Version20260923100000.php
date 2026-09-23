<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

final class Version20260923100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Prépare la publication des profils Avocat et Cabinet dans le futur annuaire.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lawyer_profile ADD uuid BINARY(16) DEFAULT NULL, ADD directory_visible TINYINT(1) DEFAULT 0 NOT NULL, ADD professional_email VARCHAR(255) DEFAULT NULL, ADD professional_phone VARCHAR(80) DEFAULT NULL, ADD portrait_media_id INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7C3D0B5DD17F50A6 ON lawyer_profile (uuid)');
        $this->addSql('CREATE INDEX idx_lawyer_directory_visible ON lawyer_profile (directory_visible)');
        foreach ($this->connection->fetchFirstColumn('SELECT id FROM lawyer_profile WHERE uuid IS NULL') as $id) {
            $this->connection->update('lawyer_profile', ['uuid' => Uuid::v7()->toBinary()], ['id' => (int) $id]);
        }
        $this->addSql('ALTER TABLE lawyer_profile MODIFY uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE cabinet ADD directory_visible TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX idx_cabinet_directory_visible ON cabinet (directory_visible)');
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
