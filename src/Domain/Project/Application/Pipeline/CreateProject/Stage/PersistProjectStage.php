<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Pipeline\CreateProject\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.project.create', attributes: ['priority' => 100])]
final class PersistProjectStage extends AbstractPersistStage {}
