<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Data;

final class QuoteFormData
{
    public string $customerId = '';
    public string $projectId = '';
    public string $issuedAt = '';
    public string $validUntil = '';
    public string $currency = 'PLN';
    public string $defaultTaxRate = '23';
    public string $notes = '';
    public string $introText = '';

    /** @var array<int, array{description: string, quantity: string, unit: string, unitPrice: string, taxRate: string}> */
    public array $items = [];
}
