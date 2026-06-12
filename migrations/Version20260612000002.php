<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed Admin role with all permissions and assign to all existing users';
    }

    public function up(Schema $schema): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $permissions = json_encode([
            'customer.list', 'customer.view', 'customer.create', 'customer.update', 'customer.delete',
            'project.list', 'project.view', 'project.create', 'project.update', 'project.delete', 'project.assign_members',
            'invoice.list', 'invoice.view', 'invoice.create', 'invoice.update', 'invoice.delete',
            'quote.list', 'quote.view', 'quote.create', 'quote.update', 'quote.delete',
            'time_record.list', 'time_record.view', 'time_record.create', 'time_record.update', 'time_record.delete',
            'time_record.view_all', 'time_record.manage_all', 'time_record.view_summary',
            'user.list', 'user.view', 'user.create', 'user.update', 'user.delete',
            'role.list', 'role.view', 'role.create', 'role.update', 'role.delete',
        ], JSON_THROW_ON_ERROR);

        $this->connection->executeStatement(
            'INSERT INTO roles (id, name, permissions, is_system, created_at, updated_at) VALUES (gen_random_uuid(), :name, :permissions, :isSystem, :createdAt, :updatedAt)',
            [
                'name'        => 'Admin',
                'permissions' => $permissions,
                'isSystem'    => true,
                'createdAt'   => $now,
                'updatedAt'   => $now,
            ],
        );

        $this->connection->executeStatement(
            'UPDATE users SET role_id = (SELECT id FROM roles WHERE is_system = TRUE LIMIT 1)',
        );
    }

    public function down(Schema $schema): void
    {
        $this->connection->executeStatement(
            'UPDATE users SET role_id = NULL WHERE role_id IN (SELECT id FROM roles WHERE is_system = TRUE)',
        );

        $this->connection->executeStatement(
            'DELETE FROM roles WHERE is_system = TRUE',
        );
    }
}
