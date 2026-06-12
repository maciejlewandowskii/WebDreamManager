<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateUser\Stage;

use App\Domain\Authorization\Application\Pipeline\UpdateUser\UpdateUserCommand;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AutoconfigureTag('app.authorization.user.update', attributes: ['priority' => 150])]
final class HashUpdatedPasswordStage implements PipelineHandlerInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateUserCommand);

        if ($payload->plainPassword !== null && $payload->plainPassword !== '') {
            $payload->user->setPassword(
                $this->hasher->hashPassword($payload->user, $payload->plainPassword),
            );
        }

        return $payload;
    }
}
