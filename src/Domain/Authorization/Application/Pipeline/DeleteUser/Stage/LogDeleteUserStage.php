<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\DeleteUser\Stage;

use App\Domain\Authorization\Application\Pipeline\DeleteUser\DeleteUserCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.user.delete', attributes: ['priority' => -200])]
final readonly class LogDeleteUserStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof DeleteUserCommand);

        $this->logUserAction(
            "User deleted: {$payload->user->getEmail()}",
            'users',
            ['id' => $payload->user->getId(), 'email' => $payload->user->getEmail(), 'name' => $payload->user->getFullName()],
        );

        return $payload;
    }
}
