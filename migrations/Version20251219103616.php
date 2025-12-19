<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219103616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation ADD seance_film_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849558E0D0F42 FOREIGN KEY (seance_film_id) REFERENCES seance_film (id)');
        $this->addSql('CREATE INDEX IDX_42C849558E0D0F42 ON reservation (seance_film_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849558E0D0F42');
        $this->addSql('DROP INDEX IDX_42C849558E0D0F42 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP seance_film_id');
    }
}
