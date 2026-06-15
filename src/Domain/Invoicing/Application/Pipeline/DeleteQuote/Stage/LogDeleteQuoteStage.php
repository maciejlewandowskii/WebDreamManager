<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\DeleteQuote\Stage;

use App\Domain\Invoicing\Application\Pipeline\DeleteQuote\DeleteQuoteCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.delete', attributes: ['priority' => -200])]
final readonly class LogDeleteQuoteStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof DeleteQuoteCommand);

        $this->logUserAction(
            "Quote deleted: #{$payload->quote->getNumber()}",
            'quotes',
            ['id' => $payload->quote->getId(), 'number' => $payload->quote->getNumber(), 'customer' => $payload->quote->getCustomer()->getName()],
        );

        return $payload;
    }
}
