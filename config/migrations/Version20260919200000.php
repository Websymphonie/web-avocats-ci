<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919200000 extends AbstractMigration
{
    public function getDescription(): string { return 'Ajoute la structure pédagogique des formations COURSE.'; }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE course_module (
              training_id INT NOT NULL,
              title VARCHAR(255) NOT NULL,
              description LONGTEXT NOT NULL,
              position INT NOT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_A21CE765D17F50A6 (uuid),
              INDEX IDX_A21CE765BEFD98D1 (training_id),
              UNIQUE INDEX uniq_course_module_training_position (training_id, position),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE lesson (
              module_id INT NOT NULL,
              title VARCHAR(255) NOT NULL,
              summary LONGTEXT DEFAULT NULL,
              position INT NOT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_F87474F3D17F50A6 (uuid),
              INDEX IDX_F87474F3AFC2B591 (module_id),
              UNIQUE INDEX uniq_lesson_module_position (module_id, position),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE course_module');
    }
}
