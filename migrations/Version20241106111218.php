<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241106111218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hotel (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', region_id INT NOT NULL, lump_sums_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', new_lump_sums_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, lump_sums_expire_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_3535ED95E237E06 (name), INDEX IDX_3535ED998260155 (region_id), INDEX IDX_3535ED986034240 (lump_sums_id), INDEX IDX_3535ED9D84FB4FF (new_lump_sums_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hotel ADD CONSTRAINT FK_3535ED998260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE hotel ADD CONSTRAINT FK_3535ED986034240 FOREIGN KEY (lump_sums_id) REFERENCES lump_sums (id)');
        $this->addSql('ALTER TABLE hotel ADD CONSTRAINT FK_3535ED9D84FB4FF FOREIGN KEY (new_lump_sums_id) REFERENCES lump_sums (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hotel DROP FOREIGN KEY FK_3535ED998260155');
        $this->addSql('ALTER TABLE hotel DROP FOREIGN KEY FK_3535ED986034240');
        $this->addSql('ALTER TABLE hotel DROP FOREIGN KEY FK_3535ED9D84FB4FF');
        $this->addSql('DROP TABLE hotel');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
