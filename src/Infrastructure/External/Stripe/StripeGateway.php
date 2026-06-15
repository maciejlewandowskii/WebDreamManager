<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Stripe;

use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\PaymentStatus;
use App\Domain\Invoicing\Port\PaymentGatewayInterface;
use App\Domain\Logging\Application\LoggerService;
use App\Domain\Logging\Entity\LogLevel;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class StripeGateway implements PaymentGatewayInterface
{
    public function __construct(
        private StripeClient $stripe,
        #[Autowire(env: 'STRIPE_PUBLISHABLE_KEY')] private string $publishableKey,
        private LoggerService $logger,
    ) {}

    public function getClientConfig(): array
    {
        return ['type' => 'stripe', 'publicKey' => $this->publishableKey];
    }

    /** @throws ApiErrorException */
    public function createEmbeddedCheckout(Invoice $invoice, string $returnUrl): string
    {
        /** @var string[] $metadata */
        $metadata = ['invoice_id' => $invoice->getId(), 'invoice_number' => $invoice->getNumber()];

        $intent = $this->stripe->paymentIntents->create([
            'amount'                    => (int) round($invoice->getGrossTotal() * 100),
            'currency'                  => strtolower($invoice->getCurrency()),
            'automatic_payment_methods' => ['enabled' => true],
            'metadata'                  => $metadata,
        ]);

        $invoice->setStripeSessionId($intent->id);

        $this->logger->externalService(
            LogLevel::Info,
            'Stripe payment intent created for invoice #' . $invoice->getNumber(),
            'stripe',
            'integration',
            ['invoice_id' => $invoice->getId(), 'amount' => $invoice->getGrossTotal(), 'currency' => $invoice->getCurrency()],
        );

        return (string) $intent->client_secret;
    }

    public function getPaymentStatus(string $sessionId): PaymentStatus
    {
        try {
            $status = $this->stripe->paymentIntents->retrieve($sessionId)->status;

            return match($status) {
                'succeeded', 'processing'                                      => PaymentStatus::Paid,
                'requires_payment_method', 'requires_confirmation',
                'requires_action', 'requires_capture', 'canceled'             => PaymentStatus::Unpaid,
                default                                                        => PaymentStatus::Unknown,
            };
        } catch (ApiErrorException) {
            return PaymentStatus::Unknown;
        }
    }

    public function cancelPayment(string $sessionId): bool
    {
        try {
            $this->stripe->paymentIntents->cancel($sessionId);

            $this->logger->externalService(
                LogLevel::Info,
                'Stripe payment intent cancelled',
                'stripe',
                'integration',
                ['session_id' => $sessionId],
            );

            return true;
        } catch (ApiErrorException $e) {
            $this->logger->externalService(
                LogLevel::Warning,
                'Stripe payment cancel failed: ' . $e->getMessage(),
                'stripe',
                'integration',
                ['session_id' => $sessionId],
            );

            return false;
        }
    }
}
