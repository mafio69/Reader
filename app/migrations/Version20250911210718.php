<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250911210718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_summary ADD COLUMN content CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__article_summary AS SELECT id, original_url, summary, created_at FROM article_summary');
        $this->addSql('DROP TABLE article_summary');
        $this->addSql('CREATE TABLE article_summary (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, original_url VARCHAR(255) NOT NULL, summary CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('INSERT INTO article_summary (id, original_url, summary, created_at) SELECT id, original_url, summary, created_at FROM __temp__article_summary');
        $this->addSql('DROP TABLE __temp__article_summary');
    }
}
