<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\CreateUser\Stage;

use App\Domain\Authorization\Application\Pipeline\CreateUser\CreateUserCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.user.create', attributes: ['priority' => -200])]
final readonly class LogCreateUserStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateUserCommand);
        assert($payload->result !== null);

        $this->logUserAction(
            "User created: {$payload->result->getEmail()}",
            'users',
            ['id' => $payload->result->getId(), 'email' => $payload->result->getEmail(), 'name' => $payload->result->getFullName()],
        );

        return $payload;
    }
}
