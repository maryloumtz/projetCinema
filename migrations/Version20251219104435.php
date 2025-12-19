<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219104435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carte_bleu (id INT AUTO_INCREMENT NOT NULL, numero_carte VARCHAR(255) NOT NULL, titulaire VARCHAR(255) NOT NULL, date_fin DATE NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_6E6DF159A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE carte_bleu ADD CONSTRAINT FK_6E6DF159A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carte_bleu DROP FOREIGN KEY FK_6E6DF159A76ED395');
        $this->addSql('DROP TABLE carte_bleu');
    }
}
