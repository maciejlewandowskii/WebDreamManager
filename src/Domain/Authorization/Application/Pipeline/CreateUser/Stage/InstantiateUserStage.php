<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\CreateUser\Stage;

use App\Domain\Authorization\Application\Pipeline\CreateUser\CreateUserCommand;
use App\Domain\Identity\Entity\User;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.user.create', attributes: ['priority' => 200])]
final class InstantiateUserStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateUserCommand);

        $user = new User($payload->data->email, $payload->data->fullName);
        $payload->data->applyTo($user);
        $payload->result = $user;

        return $payload;
    }
}
