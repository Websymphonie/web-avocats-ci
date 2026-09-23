<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les cabinets et les profils professionnels des avocats.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE cabinet (name VARCHAR(255) NOT NULL, registration_number VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(120) DEFAULT NULL, country VARCHAR(120) NOT NULL, phone VARCHAR(80) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, website_url VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_4B3A4FADD17F50A6 (uuid), INDEX IDX_4B3A4FADBF396750 (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE lawyer_profile (user_id INT NOT NULL, cabinet_id INT DEFAULT NULL, bar_number VARCHAR(120) DEFAULT NULL, professional_status VARCHAR(40) NOT NULL, specialization_summary VARCHAR(255) DEFAULT NULL, bio LONGTEXT DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_7C3D0B5DA76ED395 (user_id), INDEX IDX_7C3D0B5D1C5D7F4A (cabinet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE lawyer_profile ADD CONSTRAINT FK_7C3D0B5DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lawyer_profile ADD CONSTRAINT FK_7C3D0B5D1C5D7F4A FOREIGN KEY (cabinet_id) REFERENCES cabinet (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lawyer_profile');
        $this->addSql('DROP TABLE cabinet');
    }
}
