<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250505060102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER id SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER external_id SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER created_at SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER planned_arrival_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER city SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER "from" SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "order".id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "order".created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "order".planned_arrival_date IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_F52993989F75D7B0 ON "order" (external_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ADD PRIMARY KEY (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_16969_uniq_8d93d649e7927c74 RENAME TO UNIQ_8D93D649E7927C74
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_16955_idx_75ea56e0fb7336f0 RENAME TO IDX_75EA56E0FB7336F0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_16955_idx_75ea56e0e3bd61ce RENAME TO IDX_75EA56E0E3BD61CE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_16955_idx_75ea56e016ba31db RENAME TO IDX_75EA56E016BA31DB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_75ea56e0fb7336f0 RENAME TO idx_16955_idx_75ea56e0fb7336f0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_75ea56e016ba31db RENAME TO idx_16955_idx_75ea56e016ba31db
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_75ea56e0e3bd61ce RENAME TO idx_16955_idx_75ea56e0e3bd61ce
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_8d93d649e7927c74 RENAME TO idx_16969_uniq_8d93d649e7927c74
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_F52993989F75D7B0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" DROP CONSTRAINT "order_pkey"
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER external_id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER created_at DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER planned_arrival_date TYPE TIMESTAMP(0) WITH TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER city DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ALTER "from" DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "order".id IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "order".created_at IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "order".planned_arrival_date IS NULL
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
