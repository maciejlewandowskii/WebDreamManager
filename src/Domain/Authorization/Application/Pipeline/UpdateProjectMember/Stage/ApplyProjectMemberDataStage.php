<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateProjectMember\Stage;

use App\Domain\Authorization\Application\Pipeline\UpdateProjectMember\UpdateProjectMemberCommand;
use App\Domain\Authorization\Entity\Permission;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.project_member.update', attributes: ['priority' => 200])]
final class ApplyProjectMemberDataStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateProjectMemberCommand);

        $permissions = array_values(array_filter(
            array_map(static fn (string $v) => Permission::tryFrom($v), $payload->data->permissions),
        ));
        $payload->member->setPermissions($permissions);

        return $payload;
    }
}
