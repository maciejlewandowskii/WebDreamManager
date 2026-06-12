<?php

/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create roles and project_members tables; add role_id FK to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE roles (id UUID NOT NULL, name VARCHAR(100) NOT NULL, permissions JSON NOT NULL, is_system BOOLEAN NOT NULL DEFAULT FALSE, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B63E2EC75E237E06 ON roles (name)');

        $this->addSql('ALTER TABLE users ADD role_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_1483A5E9D60322AC ON users (role_id)');

        $this->addSql('CREATE TABLE project_members (id UUID NOT NULL, user_id UUID NOT NULL, project_id UUID NOT NULL, permissions JSON NOT NULL, created_at TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE project_members ADD CONSTRAINT FK_9B4A4D67A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_members ADD CONSTRAINT FK_9B4A4D67166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9B4A4D67A76ED395 ON project_members (user_id)');
        $this->addSql('CREATE INDEX IDX_9B4A4D67166D1F9C ON project_members (project_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_project_member ON project_members (user_id, project_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9D60322AC');
        $this->addSql('DROP INDEX IDX_1483A5E9D60322AC');
        $this->addSql('ALTER TABLE users DROP COLUMN role_id');

        $this->addSql('ALTER TABLE project_members DROP CONSTRAINT FK_9B4A4D67A76ED395');
        $this->addSql('ALTER TABLE project_members DROP CONSTRAINT FK_9B4A4D67166D1F9C');
        $this->addSql('DROP TABLE project_members');

        $this->addSql('DROP TABLE roles');
    }
}
