<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateUser\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.user.update', attributes: ['priority' => 100])]
final class PersistUpdatedUserStage extends AbstractPersistStage {}
