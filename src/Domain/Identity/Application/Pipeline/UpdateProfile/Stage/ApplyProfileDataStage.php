<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\UpdateProfile\Stage;

use App\Domain\Identity\Application\Pipeline\UpdateProfile\UpdateProfileCommand;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.profile.update', attributes: ['priority' => 200])]
final class ApplyProfileDataStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateProfileCommand);

        $payload->data->applyTo($payload->user);

        return $payload;
    }
}
