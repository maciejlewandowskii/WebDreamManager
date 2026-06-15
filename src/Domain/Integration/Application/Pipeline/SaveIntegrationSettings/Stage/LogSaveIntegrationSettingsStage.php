<?php

declare(strict_types=1);

namespace App\Domain\Integration\Application\Pipeline\SaveIntegrationSettings\Stage;

use App\Domain\Integration\Application\Pipeline\SaveIntegrationSettings\SaveIntegrationSettingsCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration.save', attributes: ['priority' => -200])]
final readonly class LogSaveIntegrationSettingsStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SaveIntegrationSettingsCommand);

        $name = $payload->integration->getName();
        $this->logUserAction(
            "Integration settings saved: {$name} (" . ($payload->enabled ? 'enabled' : 'disabled') . ')',
            'integration',
            ['integration' => $name, 'enabled' => $payload->enabled],
        );

        return $payload;
    }
}
