<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\DeleteInvoice;

use App\Domain\Invoicing\Entity\Invoice;
use App\Infrastructure\Pipeline\HasRemovableEntity;

final readonly class DeleteInvoiceCommand implements HasRemovableEntity
{
    public function __construct(public Invoice $invoice)
    {
    }

    public function getEntityToRemove(): object
    {
        return $this->invoice;
    }
}
