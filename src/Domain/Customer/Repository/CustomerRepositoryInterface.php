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

    public function save(Customer $customer, bool $flush = true): void;

    public function remove(Customer $customer, bool $flush = true): void;
}
