<?php

declare(strict_types=1);

namespace App\Domain\Integration\Application\Pipeline\SaveIntegrationSettings\Stage;

use App\Domain\Integration\Application\Pipeline\SaveIntegrationSettings\SaveIntegrationSettingsCommand;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration.save', attributes: ['priority' => 200])]
final readonly class PersistEnabledFlagStage implements PipelineHandlerInterface
{
    public function __construct(
        private SystemSettingRepositoryInterface $settings,
    ) {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SaveIntegrationSettingsCommand);

        $key = 'INTEGRATION_' . strtoupper($payload->integration->getKey()) . '_ENABLED';
        $this->settings->set($key, $payload->enabled ? '1' : '0');

        return $payload;
    }
}
