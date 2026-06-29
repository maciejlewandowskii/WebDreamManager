<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260625000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tracker fields to projects, create external_issues table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projects ADD tracker_type VARCHAR(20) DEFAULT \'none\' NOT NULL');
        $this->addSql('ALTER TABLE projects ADD tracker_resource VARCHAR(500) DEFAULT NULL');

        $this->addSql("UPDATE projects SET tracker_type = 'github', tracker_resource = github_repository WHERE github_repository IS NOT NULL");

        $this->addSql('ALTER TABLE projects DROP COLUMN github_repository');

        $this->addSql(<<<'SQL'
            CREATE TABLE external_issues (
                id               UUID          NOT NULL,
                project_id       UUID          NOT NULL,
                tracker_type     VARCHAR(20)   NOT NULL,
                external_id      VARCHAR(200)  NOT NULL,
                external_number  INT           DEFAULT NULL,
                title            VARCHAR(500)  NOT NULL,
                status           VARCHAR(50)   NOT NULL DEFAULT 'open',
                url              VARCHAR(1000) DEFAULT NULL,
                assignee         VARCHAR(200)  DEFAULT NULL,
                labels           JSON          DEFAULT NULL,
                synced_at        TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                created_at       TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at       TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('COMMENT ON COLUMN external_issues.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN external_issues.project_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN external_issues.synced_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN external_issues.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN external_issues.updated_at IS \'(DC2Type:datetime_immutable)\'');

        $this->addSql('ALTER TABLE external_issues ADD CONSTRAINT fk_ext_issues_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE UNIQUE INDEX uidx_ext_issues_unique ON external_issues (project_id, tracker_type, external_id)');
        $this->addSql('CREATE INDEX idx_ext_issues_project ON external_issues (project_id)');
        $this->addSql('CREATE INDEX idx_ext_issues_status ON external_issues (status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE external_issues DROP CONSTRAINT fk_ext_issues_project');
        $this->addSql('DROP TABLE external_issues');

        $this->addSql('ALTER TABLE projects ADD github_repository VARCHAR(500) DEFAULT NULL');
        $this->addSql("UPDATE projects SET github_repository = tracker_resource WHERE tracker_type = 'github'");
        $this->addSql('ALTER TABLE projects DROP COLUMN tracker_type');
        $this->addSql('ALTER TABLE projects DROP COLUMN tracker_resource');
    }
}
