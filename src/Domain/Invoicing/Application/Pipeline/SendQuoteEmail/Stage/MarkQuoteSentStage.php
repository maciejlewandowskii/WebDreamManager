<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\SendQuoteEmail\Stage;

use App\Domain\Invoicing\Application\Pipeline\SendQuoteEmail\SendQuoteEmailCommand;
use App\Domain\Invoicing\Entity\QuoteStatus;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.send_email', attributes: ['priority' => 100])]
final class MarkQuoteSentStage implements PipelineHandlerInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SendQuoteEmailCommand);

        if ($payload->quote->getStatus() === QuoteStatus::Draft) {
            $payload->quote->setStatus(QuoteStatus::Sent);
            $this->em->flush();
        }

        return $payload;
    }
}
