<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\GenerateInvoicePdf\Stage;

use App\Domain\Invoicing\Application\Pipeline\GenerateInvoicePdf\GenerateInvoicePdfCommand;
use App\Domain\Invoicing\Entity\InvoicePdfRecord;
use App\Infrastructure\Pdf\DocumentPdfGenerator;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.generate_pdf', attributes: ['priority' => 200])]
final class GeneratePdfFileStage implements PipelineHandlerInterface
{
    public function __construct(private readonly DocumentPdfGenerator $pdfGenerator) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof GenerateInvoicePdfCommand);

        $invoice   = $payload->invoice;
        $colorMode = $invoice->getCustomer()->getPdfColorMode()->value;
        $generated = $this->pdfGenerator->generateForInvoice($invoice, $colorMode);

        $payload->result = new InvoicePdfRecord($invoice, $colorMode, $generated->filePath, $generated->fileName);

        return $payload;
    }
}
