<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du champ genre sur film';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE film ADD genre LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE film DROP genre');
    }
}
