<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateQuote\Stage;

use App\Domain\Invoicing\Application\Pipeline\CreateQuote\CreateQuoteCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.create', attributes: ['priority' => -200])]
final readonly class LogCreateQuoteStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateQuoteCommand);
        assert($payload->result !== null);

        $this->logUserAction(
            "Quote created: #{$payload->result->getNumber()}",
            'quotes',
            ['id' => $payload->result->getId(), 'number' => $payload->result->getNumber(), 'customer' => $payload->result->getCustomer()->getName()],
        );

        return $payload;
    }
}
