<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateQuote\Stage;

use App\Domain\Invoicing\Application\Pipeline\CreateQuote\CreateQuoteCommand;
use App\Domain\LinkShortener\Application\TextLinkShortener;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.create', attributes: ['priority' => 150])]
final readonly class ShortenQuoteLinksStage implements PipelineHandlerInterface
{
    public function __construct(private TextLinkShortener $textLinkShortener)
    {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateQuoteCommand);
        assert($payload->result !== null);

        $this->textLinkShortener->shortenQuoteLinks($payload->result);

        return $payload;
    }
}
