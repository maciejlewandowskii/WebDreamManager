<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260614000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create log_entries table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE log_entries (
                id UUID NOT NULL,
                type VARCHAR(50) NOT NULL,
                level VARCHAR(20) NOT NULL,
                category VARCHAR(100) NOT NULL,
                message TEXT NOT NULL,
                context JSON DEFAULT NULL,
                user_id VARCHAR(50) DEFAULT NULL,
                user_name VARCHAR(200) DEFAULT NULL,
                service VARCHAR(100) DEFAULT NULL,
                ip_address VARCHAR(50) DEFAULT NULL,
                request_id VARCHAR(100) DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('CREATE INDEX idx_log_type ON log_entries (type)');
        $this->addSql('CREATE INDEX idx_log_level ON log_entries (level)');
        $this->addSql('CREATE INDEX idx_log_category ON log_entries (category)');
        $this->addSql('CREATE INDEX idx_log_service ON log_entries (service)');
        $this->addSql('CREATE INDEX idx_log_user_id ON log_entries (user_id)');
        $this->addSql('CREATE INDEX idx_log_created_at ON log_entries (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE log_entries');
    }
}
