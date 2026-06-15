<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\GenerateQuotePdf\Stage;

use App\Domain\Invoicing\Application\Pipeline\GenerateQuotePdf\GenerateQuotePdfCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.generate_pdf', attributes: ['priority' => -200])]
final readonly class LogGenerateQuotePdfStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof GenerateQuotePdfCommand);

        $this->logUserAction(
            "Quote PDF generated: #{$payload->quote->getNumber()}",
            'quotes',
            ['quote_id' => $payload->quote->getId(), 'number' => $payload->quote->getNumber()],
        );

        return $payload;
    }
}
