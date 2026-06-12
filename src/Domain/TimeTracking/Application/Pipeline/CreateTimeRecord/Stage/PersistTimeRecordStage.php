<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Pipeline\CreateTimeRecord\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.time_record.create', attributes: ['priority' => 100])]
final class PersistTimeRecordStage extends AbstractPersistStage {}
