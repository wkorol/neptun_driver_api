<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241108123852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Make lump_sums_id column nullable (if not already)
        $this->addSql('ALTER TABLE hotel CHANGE lump_sums_id lump_sums_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');

        // Drop the existing foreign key constraint on lump_sums_id
        $this->addSql('ALTER TABLE hotel DROP FOREIGN KEY FK_3535ED986034240');

        // Re-add the foreign key constraint on lump_sums_id with ON DELETE SET NULL
        $this->addSql('ALTER TABLE hotel ADD CONSTRAINT FK_3535ED986034240 FOREIGN KEY (lump_sums_id) REFERENCES lump_sums (id) ON DELETE SET NULL');
    }


    public function down(Schema $schema): void
    {
        // Revert lump_sums_id to non-nullable if needed
        $this->addSql('ALTER TABLE hotel CHANGE lump_sums_id lump_sums_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');

        // Drop modified foreign key constraint on lump_sums_id
        $this->addSql('ALTER TABLE hotel DROP FOREIGN KEY FK_3535ED986034240');

        // Re-add the original foreign key constraint without ON DELETE SET NULL
        $this->addSql('ALTER TABLE hotel ADD CONSTRAINT FK_3535ED986034240 FOREIGN KEY (lump_sums_id) REFERENCES lump_sums (id)');
    }


    public function isTransactional(): bool
    {
        return false;
    }
}
