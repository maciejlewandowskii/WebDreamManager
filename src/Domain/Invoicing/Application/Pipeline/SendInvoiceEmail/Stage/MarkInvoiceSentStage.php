<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail\Stage;

use App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail\SendInvoiceEmailCommand;
use App\Domain\Invoicing\Entity\InvoiceStatus;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.send_email', attributes: ['priority' => 100])]
final readonly class MarkInvoiceSentStage implements PipelineHandlerInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SendInvoiceEmailCommand);

        if ($payload->invoice->getStatus() === InvoiceStatus::Draft) {
            $payload->invoice->setStatus(InvoiceStatus::Issued);
            $this->em->flush();
        }

        return $payload;
    }
}
