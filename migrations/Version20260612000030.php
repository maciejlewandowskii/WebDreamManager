<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add notification_preferences JSON field to users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD notification_preferences JSON DEFAULT NULL');
        $this->addSql("INSERT INTO notification_rules (id, event_name, channels, required_permission, is_active, created_at) VALUES 
            (gen_random_uuid(), 'user.created', '[\"email\", \"sms\"]', NULL, true, NOW()),
            (gen_random_uuid(), 'invoice.paid', '[\"email\", \"sms\"]', NULL, true, NOW()),
            (gen_random_uuid(), 'invoice.sent', '[\"email\", \"sms\"]', NULL, true, NOW())
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP COLUMN notification_preferences');
        $this->addSql("DELETE FROM notification_rules WHERE event_name IN ('user.created', 'invoice.paid', 'invoice.sent')");
    }
}
