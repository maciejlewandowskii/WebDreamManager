<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\SendQuoteEmail\Stage;

use App\Domain\Invoicing\Application\Pipeline\SendQuoteEmail\SendQuoteEmailCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.send_email', attributes: ['priority' => -200])]
final readonly class LogSendQuoteEmailStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SendQuoteEmailCommand);

        $this->logUserAction(
            "Quote email sent: #{$payload->quote->getNumber()} to {$payload->quote->getCustomer()->getName()}",
            'quotes',
            ['quote_id' => $payload->quote->getId(), 'number' => $payload->quote->getNumber(), 'customer_email' => $payload->quote->getCustomer()->getEmail()],
        );

        return $payload;
    }
}
