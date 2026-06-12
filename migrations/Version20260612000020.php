<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notification_rules table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE notification_rules (
                id UUID NOT NULL,
                event_name VARCHAR(100) NOT NULL,
                channels JSON NOT NULL,
                required_permission VARCHAR(100) DEFAULT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('CREATE INDEX idx_notification_rule_event ON notification_rules (event_name, is_active)');
        $this->addSql("COMMENT ON COLUMN notification_rules.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN notification_rules.created_at IS '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notification_rules');
    }
}
