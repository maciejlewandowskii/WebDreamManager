<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Pipeline\UpdateTimeRecord\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.time_record.update', attributes: ['priority' => 100])]
final class PersistUpdatedTimeRecordStage extends AbstractPersistStage {}
