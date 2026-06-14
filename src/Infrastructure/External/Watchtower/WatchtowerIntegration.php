<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Watchtower;

use App\Domain\Integration\Port\IntegrationInterface;
use App\Domain\Integration\ValueObject\IntegrationField;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration', attributes: ['priority' => 5])]
final class WatchtowerIntegration implements IntegrationInterface
{
    public function getKey(): string
    {
        return 'watchtower';
    }

    public function getName(): string
    {
        return 'Watchtower';
    }

    public function getDescription(): string
    {
        return 'Automatically pull and restart containers when a new image is published. Enables one-click updates from the Version & Updates page.';
    }

    public function getIcon(): string
    {
        return 'tabler:refresh-alert';
    }

    public function getFields(): array
    {
        return [
            'WATCHTOWER_URL'   => new IntegrationField('HTTP API URL'),
            'WATCHTOWER_TOKEN' => new IntegrationField('HTTP API Token', secret: true),
        ];
    }
}
