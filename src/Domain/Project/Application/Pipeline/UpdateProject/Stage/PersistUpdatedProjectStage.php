<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Pipeline\UpdateProject\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.project.update', attributes: ['priority' => 100])]
final class PersistUpdatedProjectStage extends AbstractPersistStage {}
