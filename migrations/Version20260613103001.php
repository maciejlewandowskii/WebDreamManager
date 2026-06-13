<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260613103001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add items_snapshot column to invoices_audit and quotes_audit tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoices_audit ADD COLUMN IF NOT EXISTS items_snapshot JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE quotes_audit ADD COLUMN IF NOT EXISTS items_snapshot JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoices_audit DROP COLUMN IF EXISTS items_snapshot');
        $this->addSql('ALTER TABLE quotes_audit DROP COLUMN IF EXISTS items_snapshot');
    }
}
