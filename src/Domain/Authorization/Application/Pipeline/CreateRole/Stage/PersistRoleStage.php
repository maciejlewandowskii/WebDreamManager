<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\CreateRole\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.role.create', attributes: ['priority' => 100])]
final class PersistRoleStage extends AbstractPersistStage {}
