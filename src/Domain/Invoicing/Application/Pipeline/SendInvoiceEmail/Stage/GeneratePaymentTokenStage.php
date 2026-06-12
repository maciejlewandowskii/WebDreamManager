<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail\Stage;

use App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail\SendInvoiceEmailCommand;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Uid\Uuid;

#[AutoconfigureTag('app.invoice.send_email', attributes: ['priority' => 300])]
final readonly class GeneratePaymentTokenStage implements PipelineHandlerInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SendInvoiceEmailCommand);

        if ($payload->invoice->getPaymentToken() === null) {
            $payload->invoice->setPaymentToken(Uuid::v4()->toRfc4122());
            $this->em->flush();
        }

        return $payload;
    }
}
