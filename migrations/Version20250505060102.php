<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250505060102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema: region, lump_sums, order, service, user, messenger_messages, hotel + FKs, trigger.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE region (
                id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                position INT DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_F62F176462CE4F5 ON region (position)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE lump_sums (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                fixed_values JSON NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN lump_sums.id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE "order" (
                id UUID NOT NULL,
                external_id INT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                planned_arrival_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                status INT DEFAULT NULL,
                city VARCHAR(255) NOT NULL,
                street VARCHAR(255) DEFAULT NULL,
                house VARCHAR(255) DEFAULT NULL,
                "from" VARCHAR(255) NOT NULL,
                taxi_number VARCHAR(255) DEFAULT NULL,
                destination VARCHAR(255) DEFAULT NULL,
                notes VARCHAR(255) DEFAULT NULL,
                phone_number VARCHAR(255) DEFAULT NULL,
                company_name VARCHAR(255) DEFAULT NULL,
                price DOUBLE PRECISION DEFAULT NULL,
                passenger_count INT DEFAULT NULL,
                payment_method INT DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_F52993989F75D7B0 ON "order" (external_id)
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
            CREATE TABLE service (
                id UUID NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                description VARCHAR(255) DEFAULT NULL,
                price VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN service.id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (
                id UUID NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                roles JSON NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (
                id BIGSERIAL NOT NULL,
                body TEXT NOT NULL,
                headers TEXT NOT NULL,
                queue_name VARCHAR(190) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.available_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.delivered_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
                BEGIN
                    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER notify_trigger
            AFTER INSERT OR UPDATE ON messenger_messages
            FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE hotel (
                id UUID NOT NULL,
                region_id INT DEFAULT NULL,
                lump_sums_id UUID DEFAULT NULL,
                new_lump_sums_id UUID DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                lump_sums_expire_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                update_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_3535ED95E237E06 ON hotel (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3535ED998260155 ON hotel (region_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3535ED986034240 ON hotel (lump_sums_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3535ED9D84FB4FF ON hotel (new_lump_sums_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN hotel.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN hotel.lump_sums_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN hotel.new_lump_sums_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN hotel.lump_sums_expire_date IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN hotel.update_date IS '(DC2Type:datetime_immutable)'
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE hotel
                ADD CONSTRAINT FK_3535ED998260155
                FOREIGN KEY (region_id) REFERENCES region (id)
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hotel
                ADD CONSTRAINT FK_3535ED986034240
                FOREIGN KEY (lump_sums_id) REFERENCES lump_sums (id)
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hotel
                ADD CONSTRAINT FK_3535ED9D84FB4FF
                FOREIGN KEY (new_lump_sums_id) REFERENCES lump_sums (id)
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE hotel DROP CONSTRAINT IF EXISTS FK_3535ED998260155
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hotel DROP CONSTRAINT IF EXISTS FK_3535ED986034240
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hotel DROP CONSTRAINT IF EXISTS FK_3535ED9D84FB4FF
        SQL);

        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP FUNCTION IF EXISTS notify_messenger_messages()
        SQL);

        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS hotel
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS "order"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS service
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS region
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS lump_sums
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
