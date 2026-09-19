<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919172000 extends AbstractMigration
{
    public function getDescription(): string { return 'Permet la permutation atomique des positions d’images de galerie par Doctrine.'; }
    public function up(Schema $schema): void { $this->addSql('DROP INDEX uniq_gallery_position ON photo_gallery_item'); }
    public function down(Schema $schema): void { $this->addSql('CREATE UNIQUE INDEX uniq_gallery_position ON photo_gallery_item (gallery_id, position)'); }
}
