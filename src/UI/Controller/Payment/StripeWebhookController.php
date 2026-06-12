<?php

declare(strict_types=1);

namespace App\UI\Controller\Payment;

use App\Domain\Invoicing\Application\Pipeline\MarkInvoicePaid\MarkInvoicePaidCommand;
use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineProcessor;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StripeWebhookController extends AbstractController
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoices,
        #[Autowire(env: 'STRIPE_WEBHOOK_SECRET')] private readonly string $webhookSecret,
        #[AutowireIterator('app.invoice.mark_paid')] private readonly iterable $markPaidHandlers,
    ) {}

    #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent($payload, (string) $sigHeader, $this->webhookSecret);
        } catch (SignatureVerificationException) {
            return new Response('Invalid signature.', Response::HTTP_BAD_REQUEST);
        }

        if ($event->type === 'payment_intent.succeeded') {
            /** @var PaymentIntent $intent */
            $intent    = $event->data->object;
            $invoiceId = $intent->metadata?->invoice_id ?? null;

            if ($invoiceId !== null) {
                $invoice = $this->invoices->findById((string) $invoiceId);
                if ($invoice !== null) {
                    new PipelineProcessor($this->markPaidHandlers)->run(new MarkInvoicePaidCommand($invoice));
                }
            }
        }

        return new Response('OK');
    }
}
