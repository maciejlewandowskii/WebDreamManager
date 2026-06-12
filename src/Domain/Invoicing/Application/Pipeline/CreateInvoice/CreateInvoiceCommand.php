<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateInvoice;

use App\Domain\Invoicing\Application\Data\InvoiceFormData;
use App\Domain\Invoicing\Entity\Invoice;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class CreateInvoiceCommand implements PipelineCommandInterface
{
    public ?Invoice $result = null;

    public function __construct(public readonly InvoiceFormData $data)
    {
    }

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
