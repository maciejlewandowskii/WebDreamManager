<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\AddProjectMember\Stage;

use App\Domain\Authorization\Application\Pipeline\AddProjectMember\AddProjectMemberCommand;
use App\Domain\Authorization\Entity\Permission;
use App\Domain\Authorization\Entity\ProjectMember;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.project_member.add', attributes: ['priority' => 200])]
final class InstantiateProjectMemberStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof AddProjectMemberCommand);
        assert($payload->data->user !== null);

        $member = new ProjectMember($payload->data->user, $payload->project);

        $permissions = array_values(array_filter(
            array_map(static fn (string $v) => Permission::tryFrom($v), $payload->data->permissions),
        ));
        $member->setPermissions($permissions);

        $payload->result = $member;

        return $payload;
    }
}
