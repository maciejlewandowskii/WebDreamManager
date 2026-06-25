<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateInvoice\Stage;

use App\Domain\Invoicing\Application\Pipeline\CreateInvoice\CreateInvoiceCommand;
use App\Domain\LinkShortener\Application\TextLinkShortener;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.create', attributes: ['priority' => 150])]
final readonly class ShortenInvoiceLinksStage implements PipelineHandlerInterface
{
    public function __construct(private TextLinkShortener $textLinkShortener)
    {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateInvoiceCommand);
        assert($payload->result !== null);

        $this->textLinkShortener->shortenInvoiceLinks($payload->result);

        return $payload;
    }
}
