<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dépublie la page CGU de démonstration tant qu’aucun contenu officiel n’est validé.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "UPDATE page SET status = :draft, published_at = NULL WHERE slug = :slug AND editorial_group = :pageGroup AND status = :published AND content LIKE :demoContent",
            [
                'draft' => 'DRAFT',
                'slug' => 'conditions-generales-utilisation',
                'pageGroup' => 'LEGAL',
                'published' => 'PUBLISHED',
                'demoContent' => '%Cette page fictive sert à préparer les démonstrations%',
            ],
        );
    }

    public function down(Schema $schema): void
    {
        // Do not republish unvalidated legal copy automatically on rollback.
    }
}
