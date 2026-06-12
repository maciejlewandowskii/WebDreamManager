<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add SMS 2FA fields to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD sms_auth_enabled BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE users ADD sms_auth_code VARCHAR(6) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP COLUMN sms_auth_enabled');
        $this->addSql('ALTER TABLE users DROP COLUMN sms_auth_code');
    }
}
