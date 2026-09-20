<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919234000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add learner lesson progression for course enrollments.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lesson_progress (enrollment_id INT NOT NULL, lesson_id INT NOT NULL, status VARCHAR(20) NOT NULL, started_at DATETIME DEFAULT NULL, last_accessed_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_6A46B85FD17F50A6 (uuid), INDEX IDX_6A46B85F8F7DB25B (enrollment_id), INDEX IDX_6A46B85FCDF80196 (lesson_id), INDEX IDX_6A46B85F7B00651C (status), UNIQUE INDEX uniq_lesson_progress_enrollment_lesson (enrollment_id, lesson_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lesson_progress ADD CONSTRAINT FK_6A46B85F8F7DB25B FOREIGN KEY (enrollment_id) REFERENCES enrollment (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE lesson_progress ADD CONSTRAINT FK_6A46B85FCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lesson_progress DROP FOREIGN KEY FK_6A46B85FCDF80196');
        $this->addSql('ALTER TABLE lesson_progress DROP FOREIGN KEY FK_6A46B85F8F7DB25B');
        $this->addSql('DROP TABLE lesson_progress');
    }
}
