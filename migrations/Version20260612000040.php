<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create system_settings table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE system_settings (key VARCHAR(100) NOT NULL, value TEXT DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(key))');
        $this->addSql("COMMENT ON COLUMN system_settings.updated_at IS '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE system_settings');
    }
}
