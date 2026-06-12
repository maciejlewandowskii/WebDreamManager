<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\UpdateWorkSettings\Stage;

use App\Domain\Identity\Application\Pipeline\UpdateWorkSettings\UpdateWorkSettingsCommand;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.work_settings.update', attributes: ['priority' => 200])]
final class ApplyWorkSettingsStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateWorkSettingsCommand);

        $payload->data->applyTo($payload->user);

        return $payload;
    }
}
