<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Sms;

use App\Domain\Integration\Port\IntegrationInterface;
use App\Domain\Integration\ValueObject\IntegrationField;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration', attributes: ['priority' => 20])]
final class TwilioIntegration implements IntegrationInterface
{
    public function getKey(): string
    {
        return 'sms';
    }

    public function getName(): string
    {
        return 'Twilio SMS';
    }

    public function getDescription(): string
    {
        return 'Send SMS notifications and two-factor authentication codes via Twilio.';
    }

    public function getIcon(): string
    {
        return 'logos:twilio-icon';
    }

    public function getFields(): array
    {
        return [
            'TWILIO_DSN'         => new IntegrationField('Twilio DSN',  secret: true),
            'TWILIO_FROM_NUMBER' => new IntegrationField('From Number', secret: false),
        ];
    }
}
