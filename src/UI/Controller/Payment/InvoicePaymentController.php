<?php

declare(strict_types=1);

namespace App\UI\Controller\Payment;

use App\Domain\Integration\Application\IntegrationStatusService;
use App\Domain\Invoicing\Application\Pipeline\MarkInvoicePaid\MarkInvoicePaidCommand;
use App\Domain\Invoicing\Application\Pipeline\PrepareInvoicePayment\PrepareInvoicePaymentCommand;
use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\InvoiceStatus;
use App\Domain\Invoicing\Entity\PaymentStatus;
use App\Domain\Invoicing\Port\PaymentGatewayInterface;
use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use App\Infrastructure\Pipeline\PipelineProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pay', name: 'app_payment_')]
final class InvoicePaymentController extends AbstractController
{
    /**
     * @param iterable<PipelineHandlerInterface> $preparePaymentHandlers
     * @param iterable<PipelineHandlerInterface> $markPaidHandlers
     */
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoices,
        private readonly PaymentGatewayInterface $gateway,
        private readonly IntegrationStatusService $integrations,
        #[AutowireIterator('app.invoice.prepare_payment')] private readonly iterable $preparePaymentHandlers,
        #[AutowireIterator('app.invoice.mark_paid')] private readonly iterable $markPaidHandlers,
    ) {
    }

    #[Route('/{token}', name: 'show')]
    public function show(string $token): Response
    {
        $invoice = $this->findByToken($token);

        $stripeEnabled = $this->integrations->isEnabled('stripe');

        return $this->render('views/payment/show.html.twig', [
            'invoice'        => $invoice,
            'paymentConfig'  => $stripeEnabled ? $this->gateway->getClientConfig() : null,
            'alreadyPaid'    => $invoice->getStatus() === InvoiceStatus::Paid,
            'stripeEnabled'  => $stripeEnabled,
        ]);
    }

    #[Route('/{token}/session', name: 'session', methods: ['POST'])]
    public function createSession(string $token): JsonResponse
    {
        $invoice = $this->findByToken($token);

        if ($invoice->getStatus() === InvoiceStatus::Paid) {
            return $this->json(['error' => 'Invoice already paid.'], Response::HTTP_GONE);
        }

        $command = new PrepareInvoicePaymentCommand($invoice);
        new PipelineProcessor($this->preparePaymentHandlers)->run($command);

        return $this->json(['clientSecret' => $command->clientSecret]);
    }

    #[Route('/{token}/return', name: 'return')]
    public function return(string $token, Request $request): Response
    {
        $invoice        = $this->findByToken($token);
        $paymentIntent  = (string) $request->query->get('payment_intent', '');
        $redirectStatus = (string) $request->query->get('redirect_status', '');

        $status = match($redirectStatus) {
            'succeeded', 'processing' => PaymentStatus::Paid,
            'failed', 'canceled'      => PaymentStatus::Unpaid,
            default => $paymentIntent !== ''
                ? $this->gateway->getPaymentStatus($paymentIntent)
                : PaymentStatus::Unknown,
        };

        if ($status === PaymentStatus::Paid) {
            new PipelineProcessor($this->markPaidHandlers)->run(new MarkInvoicePaidCommand($invoice));
        }

        return $this->render('views/payment/return.html.twig', [
            'invoice' => $invoice,
            'status'  => $status->value,
        ]);
    }

    private function findByToken(string $token): Invoice
    {
        $invoice = $this->invoices->findByPaymentToken($token);

        if ($invoice === null) {
            throw $this->createNotFoundException('Payment link not found.');
        }

        return $invoice;
    }
}
