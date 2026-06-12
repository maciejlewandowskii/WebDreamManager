<?php

declare(strict_types=1);

namespace App\Domain\Project\Repository;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Project\Entity\Project;

interface ProjectRepositoryInterface
{
    public function findById(string $id): ?Project;

    /** @return Project[] */
    public function findAll(string $sortBy = 'createdAt', string $sortDirection = 'DESC'): array;

    /** @return Project[] */
    public function findByCustomer(Customer $customer): array;

    /** @return Project[] */
    public function search(string $query, string $sortBy = 'name', string $sortDirection = 'ASC'): array;

    public function save(Project $project, bool $flush = true): void;

    public function remove(Project $project, bool $flush = true): void;
}
