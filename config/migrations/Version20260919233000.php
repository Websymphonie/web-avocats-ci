<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919233000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add internal Learning foreign keys without cross-context constraints.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE course_module ADD CONSTRAINT FK_COURSE_MODULE_TRAINING FOREIGN KEY (training_id) REFERENCES training (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_LESSON_MODULE FOREIGN KEY (module_id) REFERENCES course_module (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE lesson_resource ADD CONSTRAINT FK_LESSON_RESOURCE_LESSON FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_ENROLLMENT_TRAINING FOREIGN KEY (training_id) REFERENCES training (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE live_training_details ADD CONSTRAINT FK_LIVE_DETAILS_TRAINING FOREIGN KEY (training_id) REFERENCES training (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE live_training_details DROP FOREIGN KEY FK_LIVE_DETAILS_TRAINING');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_ENROLLMENT_TRAINING');
        $this->addSql('ALTER TABLE lesson_resource DROP FOREIGN KEY FK_LESSON_RESOURCE_LESSON');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_LESSON_MODULE');
        $this->addSql('ALTER TABLE course_module DROP FOREIGN KEY FK_COURSE_MODULE_TRAINING');
    }
}
