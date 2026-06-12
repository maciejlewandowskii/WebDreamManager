<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\MarkInvoicePaid\Stage;

use App\Domain\Invoicing\Application\Pipeline\MarkInvoicePaid\MarkInvoicePaidCommand;
use App\Domain\Invoicing\Entity\InvoiceStatus;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.mark_paid', attributes: ['priority' => 100])]
final readonly class SetInvoicePaidStage implements PipelineHandlerInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof MarkInvoicePaidCommand);

        if ($payload->invoice->getStatus() !== InvoiceStatus::Paid) {
            $payload->invoice->setStatus(InvoiceStatus::Paid);
            $this->em->flush();
        }

        return $payload;
    }
}
