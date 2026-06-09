<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Repository;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\InvoiceStatus;

interface InvoiceRepositoryInterface
{
    public function findById(string $id): ?Invoice;

    public function findByNumber(string $number): ?Invoice;

    /** @return Invoice[] */
    public function findByCustomer(Customer $customer): array;

    /** @return Invoice[] */
    public function findByStatus(InvoiceStatus $status): array;

    /** @return Invoice[] */
    public function findAll(): array;

    public function getNextNumber(): string;

    public function save(Invoice $invoice, bool $flush = true): void;

    public function remove(Invoice $invoice, bool $flush = true): void;
}
