<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\IrreversibleMigration;

final class Version20260923120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rend les profils annuaire autonomes, conserve leur provenance et prend en charge plusieurs téléphones Cabinet.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lawyer_profile ADD display_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE lawyer_profile profile INNER JOIN user account ON account.id = profile.user_id SET profile.display_name = account.name');
        $this->addSql('ALTER TABLE lawyer_profile MODIFY display_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE lawyer_profile ADD legacy_source_uuid BINARY(16) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8EDD0691C5F53C6 ON lawyer_profile (legacy_source_uuid)');

        $this->addSql('ALTER TABLE lawyer_profile DROP FOREIGN KEY FK_7C3D0B5DA76ED395');
        $this->addSql('ALTER TABLE lawyer_profile MODIFY user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lawyer_profile ADD CONSTRAINT FK_7C3D0B5DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql("ALTER TABLE lawyer_profile MODIFY professional_status VARCHAR(40) NOT NULL DEFAULT 'UNKNOWN'");

        $this->addSql('ALTER TABLE cabinet ADD legacy_source_uuid BINARY(16) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4CED05B01C5F53C6 ON cabinet (legacy_source_uuid)');
        $this->addSql('ALTER TABLE cabinet ADD phones JSON DEFAULT NULL');
        $this->addSql("UPDATE cabinet SET phones = CASE WHEN phone IS NULL OR TRIM(phone) = '' THEN JSON_ARRAY() ELSE JSON_ARRAY(TRIM(phone)) END");
        $this->addSql('ALTER TABLE cabinet MODIFY phones JSON NOT NULL');
        $this->addSql('ALTER TABLE cabinet DROP phone');
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->fetchOne('SELECT 1 FROM lawyer_profile WHERE user_id IS NULL LIMIT 1') !== false) {
            throw new IrreversibleMigration('Impossible de rendre user_id obligatoire : des profils annuaire sans compte existent.');
        }

        if ($this->connection->fetchOne("SELECT 1 FROM lawyer_profile profile INNER JOIN user account ON account.id = profile.user_id WHERE profile.display_name <> account.name LIMIT 1") !== false) {
            throw new IrreversibleMigration('Impossible de supprimer display_name : des noms professionnels sont indépendants du nom de compte.');
        }

        if ($this->connection->fetchOne('SELECT 1 FROM cabinet WHERE JSON_LENGTH(phones) > 1 LIMIT 1') !== false) {
            throw new IrreversibleMigration('Impossible de revenir au téléphone unique : certains Cabinets possèdent plusieurs numéros.');
        }

        $this->addSql('ALTER TABLE cabinet ADD phone VARCHAR(80) DEFAULT NULL');
        $this->addSql("UPDATE cabinet SET phone = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(phones, '$[0]')), '')");
        $this->addSql('ALTER TABLE cabinet DROP phones');
        $this->addSql('DROP INDEX UNIQ_4CED05B01C5F53C6 ON cabinet');
        $this->addSql('ALTER TABLE cabinet DROP legacy_source_uuid');

        $this->addSql('ALTER TABLE lawyer_profile DROP FOREIGN KEY FK_7C3D0B5DA76ED395');
        $this->addSql('ALTER TABLE lawyer_profile MODIFY user_id INT NOT NULL');
        $this->addSql('ALTER TABLE lawyer_profile ADD CONSTRAINT FK_7C3D0B5DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql("ALTER TABLE lawyer_profile MODIFY professional_status VARCHAR(40) NOT NULL DEFAULT 'ACTIVE'");
        $this->addSql('DROP INDEX UNIQ_8EDD0691C5F53C6 ON lawyer_profile');
        $this->addSql('ALTER TABLE lawyer_profile DROP legacy_source_uuid');
        $this->addSql('ALTER TABLE lawyer_profile DROP display_name');
    }
}
