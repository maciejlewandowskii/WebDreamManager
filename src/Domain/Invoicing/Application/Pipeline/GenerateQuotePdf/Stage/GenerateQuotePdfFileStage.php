<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\GenerateQuotePdf\Stage;

use App\Domain\Invoicing\Application\Pipeline\GenerateQuotePdf\GenerateQuotePdfCommand;
use App\Domain\Invoicing\Entity\QuotePdfRecord;
use App\Infrastructure\Pdf\DocumentPdfGenerator;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.generate_pdf', attributes: ['priority' => 200])]
final class GenerateQuotePdfFileStage implements PipelineHandlerInterface
{
    public function __construct(private readonly DocumentPdfGenerator $pdfGenerator) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof GenerateQuotePdfCommand);

        $quote     = $payload->quote;
        $colorMode = $quote->getCustomer()->getPdfColorMode()->value;
        $generated = $this->pdfGenerator->generateForQuote($quote, $colorMode);

        $payload->result = new QuotePdfRecord($quote, $colorMode, $generated->filePath, $generated->fileName);

        return $payload;
    }
}
