<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260625000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace time_records.github_issue_id with external_issue_id FK';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE time_records ADD external_issue_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN time_records.external_issue_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE time_records ADD CONSTRAINT fk_time_records_ext_issue FOREIGN KEY (external_issue_id) REFERENCES external_issues (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_time_records_ext_issue ON time_records (external_issue_id)');
        $this->addSql('ALTER TABLE time_records DROP COLUMN github_issue_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE time_records DROP CONSTRAINT fk_time_records_ext_issue');
        $this->addSql('DROP INDEX idx_time_records_ext_issue');
        $this->addSql('ALTER TABLE time_records DROP COLUMN external_issue_id');
        $this->addSql('ALTER TABLE time_records ADD github_issue_id INT DEFAULT NULL');
    }
}
