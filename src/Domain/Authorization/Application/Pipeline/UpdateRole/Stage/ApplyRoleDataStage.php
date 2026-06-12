<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateRole\Stage;

use App\Domain\Authorization\Application\Pipeline\UpdateRole\UpdateRoleCommand;
use App\Domain\Authorization\Entity\Permission;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.role.update', attributes: ['priority' => 200])]
final class ApplyRoleDataStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateRoleCommand);

        if ($payload->role->isSystem()) {
            return $payload;
        }

        $payload->role->setName($payload->data->name);
        $payload->role->setPermissions(array_values(array_filter(
            array_map(static fn (string $v) => Permission::tryFrom($v), $payload->data->permissions),
        )));

        return $payload;
    }
}
