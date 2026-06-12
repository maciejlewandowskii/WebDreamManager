<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\PrepareInvoicePayment;

use App\Domain\Invoicing\Entity\Invoice;

final class PrepareInvoicePaymentCommand
{
    public ?string $clientSecret = null;

    public function __construct(public readonly Invoice $invoice) {}
}
