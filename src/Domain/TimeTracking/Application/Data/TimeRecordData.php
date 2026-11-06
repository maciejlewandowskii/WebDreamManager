<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Data;

use App\Domain\Project\Entity\Project;
use App\Domain\TimeTracking\Entity\TimeRecord;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

final class TimeRecordData
{
    #[Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(min: 2, max: 300)])]
    public string $title = '';

    #[Assert\NotNull(message: 'Please select a project.')]
    public ?Project $project = null;

    public ?string $description = null;

    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    #[Assert\LessThan(10000)]
    public ?float $spentHours = 0.0;

    #[Assert\PositiveOrZero]
    #[Assert\LessThan(10000)]
    public ?float $estimatedHours = null;

    #[Assert\NotNull]
    public ?DateTimeImmutable $date;

    #[Assert\Positive]
    public ?int $githubIssueId = null;

    public function __construct()
    {
        $this->date = new DateTimeImmutable('today');
    }

    public static function fromEntity(TimeRecord $record): self
    {
        $data = new self();
        $data->title          = $record->getTitle();
        $data->project        = $record->getProject();
        $data->description    = $record->getDescription();
        $data->spentHours     = (float) $record->getSpentHours();
        $data->estimatedHours = $record->getEstimatedHours() !== null ? (float) $record->getEstimatedHours() : null;
        $data->date           = $record->getDate();
        $data->githubIssueId  = $record->getGithubIssueId();

        return $data;
    }

    public function applyTo(TimeRecord $record): void
    {
        $record->setTitle($this->title);
        if ($this->project !== null) {
            $record->setProject($this->project);
        }
        $record->setDescription($this->description !== '' ? $this->description : null);
        $record->setSpentHours((string) ($this->spentHours ?? 0.0));
        $record->setEstimatedHours($this->estimatedHours !== null ? (string) $this->estimatedHours : null);
        if ($this->date !== null) {
            $record->setDate($this->date);
        }
        $record->setGithubIssueId($this->githubIssueId);
    }
}
