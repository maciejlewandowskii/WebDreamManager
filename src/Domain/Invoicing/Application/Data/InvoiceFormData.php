<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Data;

use Symfony\Component\Validator\Constraints as Assert;

final class InvoiceFormData
{
    #[Assert\NotBlank(message: 'Please select a customer.')]
    public string $customerId = '';

    public string $projectId = '';

    #[Assert\Sequentially([new Assert\NotBlank(), new Assert\Date()])]
    public string $issuedAt = '';

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Date(),
        new Assert\GreaterThanOrEqual(propertyPath: 'issuedAt', message: 'Due date cannot be before the issue date.'),
    ])]
    public string $dueAt = '';

    #[Assert\NotBlank]
    #[Assert\Length(exactly: 3)]
    public string $currency = 'PLN';

    #[Assert\Sequentially([new Assert\NotBlank(), new Assert\Type('numeric'), new Assert\Range(min: 0, max: 100)])]
    public string $defaultTaxRate = '23';

    public string $notes = '';

    public string $paymentTerms = '';

    #[Assert\Length(max: 200)]
    public string $bankAccount = '';

    /** @var array<int, array{description: string, quantity: string, unit: string, unitPrice: string, taxRate: string}> */
    #[Assert\Count(min: 1, minMessage: 'Add at least one line item.')]
    #[Assert\All([
        new Assert\Collection(fields: [
            'description' => [new Assert\NotBlank(message: 'Description is required.'), new Assert\Length(max: 500)],
            'quantity'    => new Assert\Sequentially([new Assert\NotBlank(), new Assert\Type('numeric'), new Assert\PositiveOrZero()]),
            'unit'        => [new Assert\NotBlank(), new Assert\Length(max: 20)],
            'unitPrice'   => new Assert\Sequentially([new Assert\NotBlank(), new Assert\Type('numeric'), new Assert\PositiveOrZero()]),
            'taxRate'     => new Assert\Sequentially([new Assert\NotBlank(), new Assert\Type('numeric'), new Assert\Range(min: 0, max: 100)]),
        ]),
    ])]
    public array $items = [];
}
