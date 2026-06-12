<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\DeleteUser\Stage;

use App\Infrastructure\Pipeline\AbstractRemoveStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.user.delete', attributes: ['priority' => 100])]
final class RemoveUserStage extends AbstractRemoveStage {}
