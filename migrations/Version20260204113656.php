<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260204113656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add hotel.old_name and backfill for Grano split.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hotel ADD COLUMN IF NOT EXISTS old_name VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE hotel SET name = 'GRANO HOTEL OLD TOWN', old_name = 'Grano' WHERE name = 'GRANO HOTEL OLD TOWN - Grano'");
        $this->addSql("UPDATE hotel SET name = 'GRANO LIFE', old_name = 'Number One' WHERE name = 'GRANO LIFE - Number One'");
        $this->addSql("UPDATE hotel SET name = 'GRANO HOTEL RIVERSIDE', old_name = 'Almond' WHERE name = 'GRANO HOTEL RIVERSIDE - Almond'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hotel DROP old_name');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
