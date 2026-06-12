<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Pipeline\DeleteTimeRecord\Stage;

use App\Infrastructure\Pipeline\AbstractRemoveStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.time_record.delete', attributes: ['priority' => 100])]
final class RemoveTimeRecordStage extends AbstractRemoveStage {}
