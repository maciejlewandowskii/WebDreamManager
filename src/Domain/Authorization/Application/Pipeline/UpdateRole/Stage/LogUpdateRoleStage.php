<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateRole\Stage;

use App\Domain\Authorization\Application\Pipeline\UpdateRole\UpdateRoleCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.role.update', attributes: ['priority' => -200])]
final readonly class LogUpdateRoleStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateRoleCommand);

        $this->logUserAction(
            "Role updated: {$payload->role->getName()}",
            'roles',
            ['id' => $payload->role->getId(), 'name' => $payload->role->getName()],
        );

        return $payload;
    }
}
