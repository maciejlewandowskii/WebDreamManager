<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Google OAuth refresh token to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD google_refresh_token TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP COLUMN google_refresh_token');
    }
}
