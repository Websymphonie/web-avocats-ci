<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919220000 extends AbstractMigration
{
    public function getDescription(): string { return 'Ajoute les inscriptions Learning et leur contrôle d’accès.'; }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE enrollment (
              training_id INT NOT NULL,
              user_id INT NOT NULL,
              status VARCHAR(20) NOT NULL,
              source VARCHAR(20) NOT NULL,
              activated_at DATETIME DEFAULT NULL,
              revoked_at DATETIME DEFAULT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_ENROLLMENT_UUID (uuid),
              UNIQUE INDEX uniq_enrollment_training_user (training_id, user_id),
              INDEX IDX_ENROLLMENT_TRAINING (training_id),
              INDEX IDX_ENROLLMENT_USER (user_id),
              INDEX IDX_ENROLLMENT_STATUS (status),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE enrollment');
    }
}
