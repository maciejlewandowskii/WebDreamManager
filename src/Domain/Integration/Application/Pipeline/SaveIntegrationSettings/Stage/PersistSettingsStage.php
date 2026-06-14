<?php

declare(strict_types=1);

namespace App\Domain\Integration\Application\Pipeline\SaveIntegrationSettings\Stage;

use App\Domain\Integration\Application\Pipeline\SaveIntegrationSettings\SaveIntegrationSettingsCommand;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration.save', attributes: ['priority' => 100])]
final readonly class PersistSettingsStage implements PipelineHandlerInterface
{
    public function __construct(
        private SystemSettingRepositoryInterface $settings,
    ) {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SaveIntegrationSettingsCommand);

        foreach ($payload->integration->getFields() as $settingKey => $field) {
            if (!array_key_exists($settingKey, $payload->values)) {
                continue;
            }

            $value = $payload->values[$settingKey];

            if ($value === '' && $field->secret) {
                continue;
            }

            $this->settings->set($settingKey, $value !== '' ? $value : null);
        }

        return $payload;
    }
}
