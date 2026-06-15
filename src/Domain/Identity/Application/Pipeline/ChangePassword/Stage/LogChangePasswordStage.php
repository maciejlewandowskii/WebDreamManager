<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\ChangePassword\Stage;

use App\Domain\Identity\Application\Pipeline\ChangePassword\ChangePasswordCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.password.change', attributes: ['priority' => -200])]
final readonly class LogChangePasswordStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof ChangePasswordCommand);

        $this->logUserAction(
            "Password changed: {$payload->user->getEmail()}",
            'security',
            ['user_id' => $payload->user->getId(), 'email' => $payload->user->getEmail()],
        );

        return $payload;
    }
}
