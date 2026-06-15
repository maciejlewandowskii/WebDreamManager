<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\UpdateWorkSettings\Stage;

use App\Domain\Identity\Application\Pipeline\UpdateWorkSettings\UpdateWorkSettingsCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.work_settings.update', attributes: ['priority' => -200])]
final readonly class LogUpdateWorkSettingsStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateWorkSettingsCommand);

        $this->logUserAction(
            "Work settings updated: {$payload->user->getEmail()}",
            'identity',
            ['user_id' => $payload->user->getId()],
        );

        return $payload;
    }
}
