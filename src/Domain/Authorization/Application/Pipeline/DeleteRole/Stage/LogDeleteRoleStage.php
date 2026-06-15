<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\DeleteRole\Stage;

use App\Domain\Authorization\Application\Pipeline\DeleteRole\DeleteRoleCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.role.delete', attributes: ['priority' => -200])]
final readonly class LogDeleteRoleStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof DeleteRoleCommand);

        $this->logUserAction(
            "Role deleted: {$payload->role->getName()}",
            'roles',
            ['id' => $payload->role->getId(), 'name' => $payload->role->getName()],
        );

        return $payload;
    }
}
