<?php

declare(strict_types=1);

namespace App\UI\Component\TimeTracking;

use App\Domain\Identity\Entity\User;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\TimeTracking\Entity\TimeRecord;
use App\Domain\TimeTracking\Repository\TimeRecordRepositoryInterface;
use DateTimeImmutable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[AsLiveComponent]
final class TimeRecordForm
{
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    #[LiveProp(writable: true)]
    #[Assert\NotBlank]
    public string $title = '';

    #[LiveProp(writable: true)]
    public string $description = '';

    #[LiveProp(writable: true)]
    #[Assert\NotBlank]
    public string $projectId = '';

    #[LiveProp(writable: true)]
    public string $estimatedHours = '';

    #[LiveProp(writable: true)]
    #[Assert\NotBlank]
    public string $spentHours = '0';

    #[LiveProp(writable: true)]
    public string $date = '';

    #[LiveProp(writable: true)]
    public string $githubIssueId = '';

    #[LiveProp]
    public ?string $recordId = null;

    private ?TimeRecord $existingRecord = null;

    public function __construct(
        private readonly TimeRecordRepositoryInterface $recordRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly Security $security,
    ) {
    }

    public function mount(?string $recordId = null): void
    {
        $this->date = (new DateTimeImmutable('today'))->format('Y-m-d');

        if ($recordId !== null) {
            $this->recordId = $recordId;
            $record = $this->recordRepository->findById($recordId);
            if ($record !== null) {
                $this->existingRecord   = $record;
                $this->title            = $record->getTitle();
                $this->description      = $record->getDescription() ?? '';
                $this->projectId        = $record->getProject()->getId();
                $this->estimatedHours   = $record->getEstimatedHours() ?? '';
                $this->spentHours       = $record->getSpentHours();
                $this->date             = $record->getDate()->format('Y-m-d');
                $this->githubIssueId    = (string) ($record->getGithubIssueId() ?? '');
            }
        }
    }

    /** @return array<string, string> */
    public function getAvailableProjects(): array
    {
        $projects = [];
        foreach ($this->projectRepository->findAll() as $project) {
            $projects[$project->getId()] = $project->getName();
        }
        return $projects;
    }

    #[LiveAction]
    public function save(): void
    {
        $this->validate();

        $project = $this->projectRepository->findById($this->projectId);
        $user = $this->security->getUser();

        if ($project === null || $user === null) {
            return;
        }

        /** @var User $user */
        if ($this->recordId !== null && $this->existingRecord !== null) {
            $record = $this->existingRecord;
        } else {
            $record = new TimeRecord($this->title, $project, $user);
        }

        $record->setTitle($this->title);
        $record->setDescription($this->description !== '' ? $this->description : null);
        $record->setProject($project);
        $record->setEstimatedHours($this->estimatedHours !== '' ? $this->estimatedHours : null);
        $record->setSpentHours($this->spentHours);
        $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $this->date);
        $record->setDate($parsedDate !== false ? $parsedDate : new DateTimeImmutable('today'));
        $record->setGithubIssueId($this->githubIssueId !== '' ? (int) $this->githubIssueId : null);

        $this->recordRepository->save($record);
    }
}
