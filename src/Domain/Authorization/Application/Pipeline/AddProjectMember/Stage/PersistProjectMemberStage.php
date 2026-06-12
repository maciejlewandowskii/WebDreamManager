<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\AddProjectMember\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.project_member.add', attributes: ['priority' => 100])]
final class PersistProjectMemberStage extends AbstractPersistStage {}
