<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260101000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout tmdb_id et statut sur film, date sur seance, version sur seance_film';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE film ADD tmdb_id INT DEFAULT NULL, ADD status VARCHAR(20) NOT NULL");
        $this->addSql("UPDATE film SET status = 'archive' WHERE status IS NULL OR status = ''");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FILM_TMDB_ID ON film (tmdb_id)');

        $this->addSql('ALTER TABLE seance ADD date DATE DEFAULT NULL');
        $this->addSql('UPDATE seance SET date = CURDATE() WHERE date IS NULL');
        $this->addSql('ALTER TABLE seance MODIFY date DATE NOT NULL');

        $this->addSql("ALTER TABLE seance_film ADD version VARCHAR(10) DEFAULT 'VF' NOT NULL");
        $this->addSql("UPDATE seance_film SET version = 'VF' WHERE version IS NULL OR version = ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE seance DROP date');
        $this->addSql('ALTER TABLE seance_film DROP version');
        $this->addSql('DROP INDEX UNIQ_FILM_TMDB_ID ON film');
        $this->addSql('ALTER TABLE film DROP tmdb_id, DROP status');
    }
}
