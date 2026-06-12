<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\UpdateWorkSettings\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.work_settings.update', attributes: ['priority' => 100])]
final class PersistWorkSettingsStage extends AbstractPersistStage {}
