<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Repository;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Invoicing\Entity\Quote;
use App\Domain\Invoicing\Entity\QuoteStatus;

interface QuoteRepositoryInterface
{
    public function findById(string $id): ?Quote;

    public function findByNumber(string $number): ?Quote;

    /** @return Quote[] */
    public function findByCustomer(Customer $customer): array;

    /** @return Quote[] */
    public function findByStatus(QuoteStatus $status): array;

    /** @return Quote[] */
    public function findAll(): array;

    /** @return Quote[] */
    public function findFiltered(
        ?string $search,
        string $sortBy = 'createdAt',
        string $sortDirection = 'DESC',
        int $offset = 0,
        int $limit = 0,
    ): array;

    public function countFiltered(?string $search): int;

    public function getNextNumber(): string;

    public function save(Quote $quote, bool $flush = true): void;

    public function remove(Quote $quote, bool $flush = true): void;
}
