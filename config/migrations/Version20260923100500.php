<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260923100500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sépare la page CARPA du groupe BAR et autorise les slugs par groupe éditorial.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_140AB620989D9B62 ON page');
        $this->addSql("UPDATE page SET title = 'Présentation', slug = 'presentation', editorial_group = 'CARPA', sort_order = 10 WHERE slug = 'carpa' AND editorial_group = 'BAR'");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PAGE_GROUP_SLUG ON page (editorial_group, slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE page SET title = 'CARPA', slug = 'carpa', editorial_group = 'BAR', sort_order = 60 WHERE slug = 'presentation' AND editorial_group = 'CARPA'");
        $this->addSql('DROP INDEX UNIQ_PAGE_GROUP_SLUG ON page');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620989D9B62 ON page (slug)');
    }
}
