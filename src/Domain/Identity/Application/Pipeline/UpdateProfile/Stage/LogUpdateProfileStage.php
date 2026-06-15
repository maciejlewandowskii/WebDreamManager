<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\UpdateProfile\Stage;

use App\Domain\Identity\Application\Pipeline\UpdateProfile\UpdateProfileCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.profile.update', attributes: ['priority' => -200])]
final readonly class LogUpdateProfileStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateProfileCommand);

        $this->logUserAction(
            "Profile updated: {$payload->user->getEmail()}",
            'identity',
            ['user_id' => $payload->user->getId(), 'email' => $payload->user->getEmail()],
        );

        return $payload;
    }
}
