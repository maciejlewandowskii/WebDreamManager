<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail\Stage;

use App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail\SendInvoiceEmailCommand;
use App\Domain\Notifications\Application\NotificationDispatcher;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.send_email', attributes: ['priority' => -100])]
final readonly class DispatchInvoiceSentNotificationStage implements PipelineHandlerInterface
{
    public function __construct(private NotificationDispatcher $dispatcher) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SendInvoiceEmailCommand);

        $invoice  = $payload->invoice;
        $customer = $invoice->getCustomer();

        $this->dispatcher->dispatch(
            eventName: 'invoice.sent',
            emailSubject: sprintf('Invoice %s sent to %s', $invoice->getNumber(), $customer->getName()),
            emailTemplate: 'notifications/invoice_sent.html.twig',
            smsText: sprintf('Invoice %s has been sent to %s.', $invoice->getNumber(), $customer->getName()),
            templateContext: ['invoice' => $invoice, 'customer' => $customer],
        );

        return $payload;
    }
}
