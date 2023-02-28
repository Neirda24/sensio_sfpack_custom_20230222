<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230227153729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[Movie] Add Rated column';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie ADD COLUMN rated VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__movie AS SELECT id, slug, title, poster, released_at FROM movie');
        $this->addSql('DROP TABLE movie');
        $this->addSql('CREATE TABLE movie (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, poster VARCHAR(255) NOT NULL, released_at DATETIME NOT NULL --(DC2Type:datetimetz_immutable)
        )');
        $this->addSql('INSERT INTO movie (id, slug, title, poster, released_at) SELECT id, slug, title, poster, released_at FROM __temp__movie');
        $this->addSql('DROP TABLE __temp__movie');
    }
}
