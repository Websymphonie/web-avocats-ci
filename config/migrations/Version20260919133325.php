<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260919133325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les catégories d’actualités, les tags génériques et leurs associations avec les actualités.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE news_category (
              name VARCHAR(150) NOT NULL,
              slug VARCHAR(180) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_4F72BA90989D9B62 (slug),
              UNIQUE INDEX UNIQ_4F72BA90D17F50A6 (uuid),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE news_news_category (
              news_entity_id INT NOT NULL,
              news_category_entity_id INT NOT NULL,
              INDEX IDX_1A91D6D69D00229C (news_entity_id),
              INDEX IDX_1A91D6D68C6E8DD7 (news_category_entity_id),
              PRIMARY KEY (
                news_entity_id, news_category_entity_id
              )
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE news_tag (
              news_entity_id INT NOT NULL,
              tag_entity_id INT NOT NULL,
              INDEX IDX_BE3ED8A19D00229C (news_entity_id),
              INDEX IDX_BE3ED8A19012477A (tag_entity_id),
              PRIMARY KEY (news_entity_id, tag_entity_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tag (
              name VARCHAR(150) NOT NULL,
              slug VARCHAR(180) NOT NULL,
              id INT AUTO_INCREMENT NOT NULL,
              uuid BINARY(16) DEFAULT NULL,
              created_at DATETIME DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_389B783989D9B62 (slug),
              UNIQUE INDEX UNIQ_389B783D17F50A6 (uuid),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              news_news_category
            ADD
              CONSTRAINT FK_1A91D6D69D00229C FOREIGN KEY (news_entity_id) REFERENCES news_entity (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              news_news_category
            ADD
              CONSTRAINT FK_1A91D6D68C6E8DD7 FOREIGN KEY (news_category_entity_id) REFERENCES news_category (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              news_tag
            ADD
              CONSTRAINT FK_BE3ED8A19D00229C FOREIGN KEY (news_entity_id) REFERENCES news_entity (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              news_tag
            ADD
              CONSTRAINT FK_BE3ED8A19012477A FOREIGN KEY (tag_entity_id) REFERENCES tag (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news_news_category DROP FOREIGN KEY FK_1A91D6D69D00229C');
        $this->addSql('ALTER TABLE news_news_category DROP FOREIGN KEY FK_1A91D6D68C6E8DD7');
        $this->addSql('ALTER TABLE news_tag DROP FOREIGN KEY FK_BE3ED8A19D00229C');
        $this->addSql('ALTER TABLE news_tag DROP FOREIGN KEY FK_BE3ED8A19012477A');
        $this->addSql('DROP TABLE news_category');
        $this->addSql('DROP TABLE news_news_category');
        $this->addSql('DROP TABLE news_tag');
        $this->addSql('DROP TABLE tag');
    }
}
