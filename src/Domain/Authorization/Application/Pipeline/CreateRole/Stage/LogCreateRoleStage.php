<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\CreateRole\Stage;

use App\Domain\Authorization\Application\Pipeline\CreateRole\CreateRoleCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.role.create', attributes: ['priority' => -200])]
final readonly class LogCreateRoleStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateRoleCommand);
        assert($payload->result !== null);

        $this->logUserAction(
            "Role created: {$payload->result->getName()}",
            'roles',
            ['id' => $payload->result->getId(), 'name' => $payload->result->getName()],
        );

        return $payload;
    }
}
