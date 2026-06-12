<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\CreateRole\Stage;

use App\Domain\Authorization\Application\Pipeline\CreateRole\CreateRoleCommand;
use App\Domain\Authorization\Entity\Permission;
use App\Domain\Authorization\Entity\Role;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.role.create', attributes: ['priority' => 200])]
final class InstantiateRoleStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateRoleCommand);

        $permissions = array_values(array_filter(
            array_map(static fn (string $v) => Permission::tryFrom($v), $payload->data->permissions),
        ));

        $role = new Role($payload->data->name);
        $role->setPermissions($permissions);
        $payload->result = $role;

        return $payload;
    }
}
