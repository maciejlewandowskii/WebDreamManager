<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add setup_token and setup_token_expires_at to users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD COLUMN setup_token VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD COLUMN setup_token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USERS_SETUP_TOKEN ON users (setup_token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS UNIQ_USERS_SETUP_TOKEN');
        $this->addSql('ALTER TABLE users DROP COLUMN setup_token');
        $this->addSql('ALTER TABLE users DROP COLUMN setup_token_expires_at');
    }
}
