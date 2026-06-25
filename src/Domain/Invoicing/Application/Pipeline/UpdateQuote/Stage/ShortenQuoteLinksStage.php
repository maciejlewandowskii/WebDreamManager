<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\UpdateQuote\Stage;

use App\Domain\Invoicing\Application\Pipeline\UpdateQuote\UpdateQuoteCommand;
use App\Domain\LinkShortener\Application\TextLinkShortener;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.update', attributes: ['priority' => 150])]
final readonly class ShortenQuoteLinksStage implements PipelineHandlerInterface
{
    public function __construct(private TextLinkShortener $textLinkShortener)
    {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateQuoteCommand);

        $this->textLinkShortener->shortenQuoteLinks($payload->quote);

        return $payload;
    }
}
