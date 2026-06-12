<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Pipeline\CreateTimeRecord;

use App\Domain\Identity\Entity\User;
use App\Domain\TimeTracking\Application\Data\TimeRecordData;
use App\Domain\TimeTracking\Entity\TimeRecord;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class CreateTimeRecordCommand implements PipelineCommandInterface
{
    public ?TimeRecord $result = null;

    public function __construct(
        public readonly TimeRecordData $data,
        public readonly User $user,
    ) {
    }

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
