<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE seance_film (id INT AUTO_INCREMENT NOT NULL, seance_id INT NOT NULL, film_id INT NOT NULL, salle_id INT NOT NULL, INDEX IDX_4ED49CF1E3797A94 (seance_id), INDEX IDX_4ED49CF1567F5183 (film_id), INDEX IDX_4ED49CF1DC304035 (salle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE seance_film ADD CONSTRAINT FK_4ED49CF1E3797A94 FOREIGN KEY (seance_id) REFERENCES seance (id)');
        $this->addSql('ALTER TABLE seance_film ADD CONSTRAINT FK_4ED49CF1567F5183 FOREIGN KEY (film_id) REFERENCES film (id)');
        $this->addSql('ALTER TABLE seance_film ADD CONSTRAINT FK_4ED49CF1DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seance_film DROP FOREIGN KEY FK_4ED49CF1E3797A94');
        $this->addSql('ALTER TABLE seance_film DROP FOREIGN KEY FK_4ED49CF1567F5183');
        $this->addSql('ALTER TABLE seance_film DROP FOREIGN KEY FK_4ED49CF1DC304035');
        $this->addSql('DROP TABLE seance_film');
    }
}
