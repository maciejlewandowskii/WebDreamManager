<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Repository;

use App\Domain\Authorization\Entity\Role;

interface RoleRepositoryInterface
{
    public function findById(string $id): ?Role;

    public function findByName(string $name): ?Role;

    /** @return Role[] */
    public function findAll(): array;

    /** @return Role[] */
    public function findFiltered(
        ?string $search,
        string $sortBy = 'name',
        string $sortDirection = 'ASC',
    ): array;

    public function findAdminRole(): ?Role;

    public function save(Role $role, bool $flush = true): void;

    public function remove(Role $role, bool $flush = true): void;
}
