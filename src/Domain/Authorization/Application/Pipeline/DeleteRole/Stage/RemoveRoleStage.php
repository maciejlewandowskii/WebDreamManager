<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\DeleteRole\Stage;

use App\Infrastructure\Pipeline\AbstractRemoveStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.role.delete', attributes: ['priority' => 100])]
final class RemoveRoleStage extends AbstractRemoveStage {}
