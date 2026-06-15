<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\UpdateQuote\Stage;

use App\Domain\Invoicing\Application\Pipeline\UpdateQuote\UpdateQuoteCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.update', attributes: ['priority' => -200])]
final readonly class LogUpdateQuoteStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateQuoteCommand);

        $this->logUserAction(
            "Quote updated: #{$payload->quote->getNumber()}",
            'quotes',
            ['id' => $payload->quote->getId(), 'number' => $payload->quote->getNumber(), 'customer' => $payload->quote->getCustomer()->getName()],
        );

        return $payload;
    }
}
