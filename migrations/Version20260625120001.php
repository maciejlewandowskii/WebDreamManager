<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260625120001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add link_shortener.view and link_shortener.manage permissions to the Admin role';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE roles SET permissions = (permissions::jsonb || '[\"link_shortener.view\",\"link_shortener.manage\"]'::jsonb)::json WHERE is_system = TRUE");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE roles SET permissions = (permissions::jsonb - 'link_shortener.view' - 'link_shortener.manage')::json WHERE is_system = TRUE");
    }
}
