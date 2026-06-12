<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateRole\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.role.update', attributes: ['priority' => 100])]
final class PersistUpdatedRoleStage extends AbstractPersistStage {}
