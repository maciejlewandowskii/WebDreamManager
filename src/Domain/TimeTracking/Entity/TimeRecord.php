<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Entity;

use App\Domain\Identity\Entity\User;
use App\Domain\Project\Entity\Project;
use App\Domain\TimeTracking\Repository\TimeRecordRepositoryInterface;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TimeRecordRepositoryInterface::class)]
#[ORM\Table(name: 'time_records')]
#[ORM\HasLifecycleCallbacks]
class TimeRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: 'string', length: 300)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 300)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Project $project;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $worker;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $estimatedHours = null;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    #[Assert\PositiveOrZero]
    private string $spentHours = '0';

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $date;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $githubIssueId = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $invoiced = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(string $title, Project $project, User $worker)
    {
        $this->title = $title;
        $this->project = $project;
        $this->worker = $worker;
        $this->date = new DateTimeImmutable('today');
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function getProject(): Project { return $this->project; }
    public function setProject(Project $project): void { $this->project = $project; }
    public function getWorker(): User { return $this->worker; }
    public function setWorker(User $worker): void { $this->worker = $worker; }
    public function getEstimatedHours(): ?string { return $this->estimatedHours; }
    public function setEstimatedHours(?string $hours): void { $this->estimatedHours = $hours; }
    public function getSpentHours(): string { return $this->spentHours; }
    public function setSpentHours(string $hours): void { $this->spentHours = $hours; }
    public function getDate(): DateTimeImmutable { return $this->date; }
    public function setDate(DateTimeImmutable $date): void { $this->date = $date; }
    public function getGithubIssueId(): ?int { return $this->githubIssueId; }
    public function setGithubIssueId(?int $issueId): void { $this->githubIssueId = $issueId; }
    public function isInvoiced(): bool { return $this->invoiced; }
    public function setInvoiced(bool $invoiced): void { $this->invoiced = $invoiced; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
