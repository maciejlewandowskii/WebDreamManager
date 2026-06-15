<?php

declare(strict_types=1);

namespace App\Domain\Logging\Application\Pipeline\CreateLogEntry;

use App\Domain\Logging\Application\Data\LogEntryData;
use App\Domain\Logging\Entity\LogEntry;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class CreateLogEntryCommand implements PipelineCommandInterface
{
    public ?LogEntry $result = null;

    public function __construct(public readonly LogEntryData $data)
    {
    }

    public static function fromData(LogEntryData $data): self
    {
        return new self($data);
    }

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
