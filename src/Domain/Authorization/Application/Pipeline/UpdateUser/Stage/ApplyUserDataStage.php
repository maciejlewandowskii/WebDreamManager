<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateUser\Stage;

use App\Domain\Authorization\Application\Pipeline\UpdateUser\UpdateUserCommand;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.user.update', attributes: ['priority' => 200])]
final class ApplyUserDataStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateUserCommand);

        $payload->data->applyTo($payload->user);

        return $payload;
    }
}
