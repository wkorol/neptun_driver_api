<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241119160806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added position for Region';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE region ADD position INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F62F176462CE4F5 ON region (position)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_F62F176462CE4F5 ON region');
        $this->addSql('ALTER TABLE region DROP position');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
