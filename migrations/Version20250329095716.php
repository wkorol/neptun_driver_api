<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250329095716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE `order` (id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', external_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', planned_arrival_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', status INT DEFAULT NULL, city VARCHAR(255) NOT NULL, street VARCHAR(255) DEFAULT NULL, house VARCHAR(255) DEFAULT NULL, `from` VARCHAR(255) NOT NULL, taxi_number VARCHAR(255) DEFAULT NULL, destination VARCHAR(255) DEFAULT NULL, notes VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, passenger_count INT DEFAULT NULL, UNIQUE INDEX UNIQ_F52993989F75D7B0 (external_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE `order`
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
