<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219100053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE film_categories (film_id INT NOT NULL, categories_id INT NOT NULL, INDEX IDX_8517EB20567F5183 (film_id), INDEX IDX_8517EB20A21214B7 (categories_id), PRIMARY KEY (film_id, categories_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE film_categories ADD CONSTRAINT FK_8517EB20567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_categories ADD CONSTRAINT FK_8517EB20A21214B7 FOREIGN KEY (categories_id) REFERENCES categories (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film_categories DROP FOREIGN KEY FK_8517EB20567F5183');
        $this->addSql('ALTER TABLE film_categories DROP FOREIGN KEY FK_8517EB20A21214B7');
        $this->addSql('DROP TABLE film_categories');
    }
}
