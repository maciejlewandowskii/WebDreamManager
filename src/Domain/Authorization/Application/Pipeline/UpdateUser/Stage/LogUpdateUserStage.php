<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateUser\Stage;

use App\Domain\Authorization\Application\Pipeline\UpdateUser\UpdateUserCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.user.update', attributes: ['priority' => -200])]
final readonly class LogUpdateUserStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateUserCommand);

        $ctx = ['id' => $payload->user->getId(), 'email' => $payload->user->getEmail()];
        if ($payload->plainPassword !== null) {
            $ctx['password_changed'] = true;
        }

        $this->logUserAction(
            "User updated: {$payload->user->getEmail()}",
            'users',
            $ctx,
        );

        return $payload;
    }
}
