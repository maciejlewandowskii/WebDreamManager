<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\RemoveProjectMember\Stage;

use App\Infrastructure\Pipeline\AbstractRemoveStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.project_member.remove', attributes: ['priority' => 100])]
final class RemoveProjectMemberStage extends AbstractRemoveStage {}
