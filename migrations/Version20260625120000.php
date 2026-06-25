<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260625120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create short_links table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE short_links (
                id UUID NOT NULL,
                code VARCHAR(20) NOT NULL,
                target_url TEXT NOT NULL,
                source_type VARCHAR(20) DEFAULT NULL,
                source_label VARCHAR(100) DEFAULT NULL,
                click_count INT NOT NULL DEFAULT 0,
                last_clicked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('CREATE UNIQUE INDEX uniq_short_links_code ON short_links (code)');
        $this->addSql('CREATE INDEX idx_short_links_created_at ON short_links (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE short_links');
    }
}
