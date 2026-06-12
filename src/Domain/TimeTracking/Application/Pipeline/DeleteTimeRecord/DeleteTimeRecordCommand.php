<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Pipeline\DeleteTimeRecord;

use App\Domain\TimeTracking\Entity\TimeRecord;
use App\Infrastructure\Pipeline\HasRemovableEntity;

final readonly class DeleteTimeRecordCommand implements HasRemovableEntity
{
    public function __construct(public TimeRecord $record)
    {
    }

    public function getEntityToRemove(): object
    {
        return $this->record;
    }
}
