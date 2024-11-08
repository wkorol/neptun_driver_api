<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241108122050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Set region_id and lump_sums_id to nullable, as generated
        $this->addSql('ALTER TABLE hotel CHANGE region_id region_id INT DEFAULT NULL, CHANGE lump_sums_id lump_sums_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');

        // Drop the existing foreign key constraint on region_id
        $this->addSql('ALTER TABLE hotel DROP FOREIGN KEY FK_3535ED998260155');

        // Re-add the foreign key constraint with ON DELETE SET NULL
        $this->addSql('ALTER TABLE hotel ADD CONSTRAINT FK_3535ED998260155 FOREIGN KEY (region_id) REFERENCES region (id) ON DELETE SET NULL');
    }


    public function down(Schema $schema): void
    {
        // Make region_id and lump_sums_id non-nullable
        $this->addSql('ALTER TABLE hotel CHANGE region_id region_id INT NOT NULL, CHANGE lump_sums_id lump_sums_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');

        // Drop modified foreign key constraint
        $this->addSql('ALTER TABLE hotel DROP FOREIGN KEY FK_3535ED998260155');

        // Re-add the original foreign key constraint without ON DELETE SET NULL
        $this->addSql('ALTER TABLE hotel ADD CONSTRAINT FK_3535ED998260155 FOREIGN KEY (region_id) REFERENCES region (id)');
    }


    public function isTransactional(): bool
    {
        return false;
    }
}
