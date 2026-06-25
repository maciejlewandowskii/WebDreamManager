<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Data;

use App\Domain\IssueTracker\Entity\ExternalIssue;
use App\Domain\Project\Entity\Project;
use App\Domain\TimeTracking\Entity\TimeRecord;
use DateTimeImmutable;

final class TimeRecordData
{
    public string $title = '';
    public ?Project $project = null;
    public ?string $description = null;
    public float $spentHours = 0.0;
    public ?float $estimatedHours = null;
    public DateTimeImmutable $date;
    public ?ExternalIssue $externalIssue = null;

    public function __construct()
    {
        $this->date = new DateTimeImmutable('today');
    }

    public static function fromEntity(TimeRecord $record): self
    {
        $data = new self();
        $data->title         = $record->getTitle();
        $data->project       = $record->getProject();
        $data->description   = $record->getDescription();
        $data->spentHours    = (float) $record->getSpentHours();
        $data->estimatedHours = $record->getEstimatedHours() !== null ? (float) $record->getEstimatedHours() : null;
        $data->date          = $record->getDate();
        $data->externalIssue = $record->getExternalIssue();

        return $data;
    }

    public function applyTo(TimeRecord $record): void
    {
        $record->setTitle($this->title);
        if ($this->project !== null) {
            $record->setProject($this->project);
        }
        $record->setDescription($this->description !== '' ? $this->description : null);
        $record->setSpentHours((string) $this->spentHours);
        $record->setEstimatedHours($this->estimatedHours !== null ? (string) $this->estimatedHours : null);
        $record->setDate($this->date);
        $record->setExternalIssue($this->externalIssue);
    }
}
