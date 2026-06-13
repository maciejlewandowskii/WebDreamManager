<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260613103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add items_snapshot column to invoices and quotes tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoices ADD COLUMN IF NOT EXISTS items_snapshot JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE quotes ADD COLUMN IF NOT EXISTS items_snapshot JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoices DROP COLUMN IF EXISTS items_snapshot');
        $this->addSql('ALTER TABLE quotes DROP COLUMN IF EXISTS items_snapshot');
    }
}
