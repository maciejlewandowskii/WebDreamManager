<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\ChangePassword\Stage;

use App\Domain\Identity\Application\Pipeline\ChangePassword\ChangePasswordCommand;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AutoconfigureTag('app.password.change', attributes: ['priority' => 200])]
final class HashAndApplyPasswordStage implements PipelineHandlerInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof ChangePasswordCommand);

        $payload->user->setPassword(
            $this->hasher->hashPassword($payload->user, $payload->newPassword)
        );

        return $payload;
    }
}
