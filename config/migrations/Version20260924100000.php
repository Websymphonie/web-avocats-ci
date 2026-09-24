<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la source de replay aux sessions LIVE et élargit les identifiants vidéo Lesson.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lesson MODIFY external_video_id VARCHAR(128) DEFAULT NULL');
        $this->addSql('ALTER TABLE live_training_details ADD replay_provider VARCHAR(255) DEFAULT NULL, ADD replay_external_id VARCHAR(128) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE live_training_details DROP replay_provider, DROP replay_external_id');
        $this->addSql('ALTER TABLE lesson MODIFY external_video_id VARCHAR(64) DEFAULT NULL');
    }
}
