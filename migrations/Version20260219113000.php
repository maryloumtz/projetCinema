<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert admin user with hashed password';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT INTO `user` (email, roles, password, telephone, nom, prenom)
            SELECT 'admin@local.test', '["ROLE_ADMIN"]', '$2y$10$vJU48jaIc6OEGKguQUeXqeKYHOWJ4Geh/ZQxDE2CUplb4yRerJTFS', NULL, 'admin', 'admin'
            WHERE NOT EXISTS (
                SELECT 1 FROM `user` WHERE email = 'admin@local.test' OR nom = 'admin'
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM `user` WHERE email = 'admin@local.test' AND nom = 'admin'");
    }
}
