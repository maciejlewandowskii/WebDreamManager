<?php

declare(strict_types=1);

namespace App\Domain\IssueTracker\Entity;

use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\IssueTracker\Enum\TrackerType;
use App\Domain\IssueTracker\Infrastructure\DoctrineExternalIssueRepository;
use App\Domain\Project\Entity\Project;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DoctrineExternalIssueRepository::class)]
#[ORM\Table(name: 'external_issues')]
#[ORM\UniqueConstraint(name: 'uidx_ext_issues_unique', columns: ['project_id', 'tracker_type', 'external_id'])]
#[ORM\Index(name: 'idx_ext_issues_project', columns: ['project_id'])]
#[ORM\Index(name: 'idx_ext_issues_status', columns: ['status'])]
#[ORM\HasLifecycleCallbacks]
class ExternalIssue
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Project $project;

    #[ORM\Column(type: 'string', length: 20, enumType: TrackerType::class)]
    private TrackerType $trackerType;

    #[ORM\Column(type: 'string', length: 200)]
    private string $externalId;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $externalNumber = null;

    #[ORM\Column(type: 'string', length: 500)]
    private string $title;

    #[ORM\Column(type: 'string', length: 50, enumType: IssueStatus::class)]
    private IssueStatus $status = IssueStatus::Open;

    #[ORM\Column(type: 'string', length: 1000, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private ?string $assignee = null;

    /** @var string[]|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $labels = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $syncedAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(Project $project, TrackerType $trackerType, string $externalId, string $title)
    {
        $this->project     = $project;
        $this->trackerType = $trackerType;
        $this->externalId  = $externalId;
        $this->title       = $title;
        $this->syncedAt    = new DateTimeImmutable();
        $this->createdAt   = new DateTimeImmutable();
        $this->updatedAt   = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): string { return (string) $this->id; }
    public function getProject(): Project { return $this->project; }
    public function getTrackerType(): TrackerType { return $this->trackerType; }
    public function getExternalId(): string { return $this->externalId; }
    public function getExternalNumber(): ?int { return $this->externalNumber; }
    public function setExternalNumber(?int $number): void { $this->externalNumber = $number; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function getStatus(): IssueStatus { return $this->status; }
    public function setStatus(IssueStatus $status): void { $this->status = $status; }
    public function getUrl(): ?string { return $this->url; }
    public function setUrl(?string $url): void { $this->url = $url; }
    public function getAssignee(): ?string { return $this->assignee; }
    public function setAssignee(?string $assignee): void { $this->assignee = $assignee; }

    /** @return string[]|null */
    public function getLabels(): ?array { return $this->labels; }

    /** @param string[]|null $labels */
    public function setLabels(?array $labels): void { $this->labels = $labels; }

    public function getSyncedAt(): DateTimeImmutable { return $this->syncedAt; }
    public function touch(): void { $this->syncedAt = new DateTimeImmutable(); }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
