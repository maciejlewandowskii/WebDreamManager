<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\UpdateInvoice;

use App\Domain\Invoicing\Application\Data\InvoiceFormData;
use App\Domain\Invoicing\Entity\Invoice;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final readonly class UpdateInvoiceCommand implements PipelineCommandInterface
{
    public function __construct(
        public Invoice $invoice,
        public InvoiceFormData $data,
    ) {
    }

    public function getEntityToSave(): object
    {
        return $this->invoice;
    }
}
