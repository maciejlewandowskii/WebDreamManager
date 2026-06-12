<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add system.view and system.manage permissions to the Admin role';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE roles SET permissions = (permissions::jsonb || '[\"system.view\",\"system.manage\"]'::jsonb)::json WHERE is_system = TRUE");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE roles SET permissions = (permissions::jsonb - 'system.view' - 'system.manage')::json WHERE is_system = TRUE");
    }
}
