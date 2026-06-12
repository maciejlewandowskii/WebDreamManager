<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Pipeline\DeleteProject\Stage;

use App\Infrastructure\Pipeline\AbstractRemoveStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.project.delete', attributes: ['priority' => 100])]
final class RemoveProjectStage extends AbstractRemoveStage {}
