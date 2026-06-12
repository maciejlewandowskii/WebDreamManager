<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\CreateUser\Stage;

use App\Domain\Authorization\Application\Pipeline\CreateUser\CreateUserCommand;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AutoconfigureTag('app.authorization.user.create', attributes: ['priority' => 150])]
final class HashUserPasswordStage implements PipelineHandlerInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateUserCommand);
        assert($payload->result !== null);

        $randomPassword = bin2hex(random_bytes(32));
        $payload->result->setPassword(
            $this->hasher->hashPassword($payload->result, $randomPassword),
        );

        $payload->result->generateSetupToken();

        return $payload;
    }
}
