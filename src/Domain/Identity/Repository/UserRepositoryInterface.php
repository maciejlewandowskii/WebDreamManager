<?php

declare(strict_types=1);

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    /** @return User[] */
    public function findAll(): array;

    /** @return User[] */
    public function findActive(): array;

    /** @return User[] */
    public function findActiveWithPermission(string $permission): array;

    /** @return User[] */
    public function findFiltered(
        ?string $search,
        string $sortBy = 'fullName',
        string $sortDirection = 'ASC',
        int $offset = 0,
        int $limit = 0,
    ): array;

    public function countFiltered(?string $search): int;

    public function findBySetupToken(string $token): ?User;

    public function save(User $user, bool $flush = true): void;

    public function remove(User $user, bool $flush = true): void;
}
