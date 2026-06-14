<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Google;

use App\Domain\Integration\Port\IntegrationInterface;
use App\Domain\Integration\ValueObject\IntegrationField;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration', attributes: ['priority' => 10])]
final class GoogleIntegration implements IntegrationInterface
{
    public function getKey(): string
    {
        return 'google';
    }

    public function getName(): string
    {
        return 'Google Calendar';
    }

    public function getDescription(): string
    {
        return 'Create Google Meet links and sync project meetings to Google Calendar.';
    }

    public function getIcon(): string
    {
        return 'logos:google-icon';
    }

    public function getFields(): array
    {
        return [
            'GOOGLE_CLIENT_ID'     => new IntegrationField('Client ID',     secret: false),
            'GOOGLE_CLIENT_SECRET' => new IntegrationField('Client Secret', secret: true),
            'GOOGLE_REDIRECT_URI'  => new IntegrationField('Redirect URI',  secret: false),
        ];
    }
}
