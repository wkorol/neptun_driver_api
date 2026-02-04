<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260204120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize GRANO HOTEL SOL MARINA name and set old_name.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE hotel SET name = 'GRANO HOTEL SOL MARINA', old_name = 'Sol Marina' WHERE name = 'GRANO HOTEL SOL MARINA - Sol Marina'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE hotel SET name = 'GRANO HOTEL SOL MARINA - Sol Marina', old_name = NULL WHERE name = 'GRANO HOTEL SOL MARINA'");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
