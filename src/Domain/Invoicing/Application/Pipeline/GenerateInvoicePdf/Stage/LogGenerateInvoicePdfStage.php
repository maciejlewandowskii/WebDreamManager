<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\GenerateInvoicePdf\Stage;

use App\Domain\Invoicing\Application\Pipeline\GenerateInvoicePdf\GenerateInvoicePdfCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.generate_pdf', attributes: ['priority' => -200])]
final readonly class LogGenerateInvoicePdfStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof GenerateInvoicePdfCommand);

        $this->logUserAction(
            "Invoice PDF generated: #{$payload->invoice->getNumber()}",
            'invoices',
            ['invoice_id' => $payload->invoice->getId(), 'number' => $payload->invoice->getNumber()],
        );

        return $payload;
    }
}
