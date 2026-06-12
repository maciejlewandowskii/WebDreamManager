<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Data;

final class InvoiceFormData
{
    public string $customerId = '';
    public string $projectId = '';
    public string $issuedAt = '';
    public string $dueAt = '';
    public string $currency = 'PLN';
    public string $defaultTaxRate = '23';
    public string $notes = '';
    public string $paymentTerms = '';
    public string $bankAccount = '';

    /** @var array<int, array{description: string, quantity: string, unit: string, unitPrice: string, taxRate: string}> */
    public array $items = [];
}
