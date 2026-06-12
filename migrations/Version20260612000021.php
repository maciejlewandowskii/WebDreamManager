<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add phone field to users table for SMS notifications';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD phone VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP COLUMN phone');
    }
}
