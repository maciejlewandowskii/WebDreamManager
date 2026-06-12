<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateProjectMember\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.project_member.update', attributes: ['priority' => 100])]
final class PersistUpdatedProjectMemberStage extends AbstractPersistStage {}
