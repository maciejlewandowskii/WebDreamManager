<?php

declare(strict_types=1);

namespace App\Domain\Customer\Repository;

use App\Domain\Customer\Entity\Customer;

interface CustomerRepositoryInterface
{
    public function findById(string $id): ?Customer;

    /** @return Customer[] */
    public function findAll(string $sortBy = 'name', string $sortDirection = 'ASC'): array;

    /** @return Customer[] */
    public function search(string $query, string $sortBy = 'name', string $sortDirection = 'ASC'): array;

    /** @return Customer[] */
    public function findFiltered(?string $search, string $sortBy = 'name', string $sortDirection = 'ASC', int $offset = 0, int $limit = 0): array;

    public function countFiltered(?string $search): int;

    public function save(Customer $customer, bool $flush = true): void;

    public function remove(Customer $customer, bool $flush = true): void;
}
