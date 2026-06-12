<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\GenerateInvoicePdf;

use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\InvoicePdfRecord;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class GenerateInvoicePdfCommand implements PipelineCommandInterface
{
    public ?InvoicePdfRecord $result = null;

    public function __construct(public readonly Invoice $invoice) {}

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
