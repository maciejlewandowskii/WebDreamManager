<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\MarkInvoicePaid\Stage;

use App\Domain\Invoicing\Application\Pipeline\MarkInvoicePaid\MarkInvoicePaidCommand;
use App\Domain\Notifications\Application\NotificationDispatcher;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.mark_paid', attributes: ['priority' => -100])]
final readonly class DispatchInvoicePaidNotificationStage implements PipelineHandlerInterface
{
    public function __construct(private NotificationDispatcher $dispatcher) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof MarkInvoicePaidCommand);

        $invoice  = $payload->invoice;
        $customer = $invoice->getCustomer();

        $this->dispatcher->dispatch(
            eventName: 'invoice.paid',
            emailSubject: sprintf('Invoice %s has been paid', $invoice->getNumber()),
            emailTemplate: 'notifications/invoice_paid.html.twig',
            smsText: sprintf('Invoice %s from %s has been paid.', $invoice->getNumber(), $customer->getName()),
            templateContext: ['invoice' => $invoice, 'customer' => $customer],
        );

        return $payload;
    }
}
