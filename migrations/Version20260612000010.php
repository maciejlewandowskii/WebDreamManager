<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enable pg_trgm extension for fuzzy/typo-tolerant search';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP EXTENSION IF EXISTS pg_trgm');
    }
}
