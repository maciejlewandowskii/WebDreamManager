<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Stripe;

use App\Domain\Integration\Port\IntegrationInterface;
use App\Domain\Integration\ValueObject\IntegrationField;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration', attributes: ['priority' => 40])]
final class StripeIntegration implements IntegrationInterface
{
    public function getKey(): string
    {
        return 'stripe';
    }

    public function getName(): string
    {
        return 'Stripe';
    }

    public function getDescription(): string
    {
        return 'Accept card payments and manage subscriptions via Stripe Checkout.';
    }

    public function getIcon(): string
    {
        return 'logos:stripe';
    }

    public function getFields(): array
    {
        return [
            'STRIPE_SECRET_KEY'      => new IntegrationField('Secret Key',      secret: true),
            'STRIPE_PUBLISHABLE_KEY' => new IntegrationField('Publishable Key', secret: false),
            'STRIPE_WEBHOOK_SECRET'  => new IntegrationField('Webhook Secret',  secret: true),
        ];
    }
}
