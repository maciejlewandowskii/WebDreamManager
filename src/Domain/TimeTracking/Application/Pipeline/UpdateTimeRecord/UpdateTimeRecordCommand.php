<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Pipeline\UpdateTimeRecord;

use App\Domain\TimeTracking\Application\Data\TimeRecordData;
use App\Domain\TimeTracking\Entity\TimeRecord;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final readonly class UpdateTimeRecordCommand implements PipelineCommandInterface
{
    public function __construct(
        public TimeRecord $record,
        public TimeRecordData $data,
    ) {
    }

    public function getEntityToSave(): object
    {
        return $this->record;
    }
}
