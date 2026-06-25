<?php

declare(strict_types=1);

namespace App\Domain\IssueTracker\Port;

use App\Domain\IssueTracker\Entity\ExternalIssue;
use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\IssueTracker\Enum\TrackerType;
use App\Domain\Project\Entity\Project;

interface ExternalIssueRepositoryInterface
{
    public function findById(string $id): ?ExternalIssue;

    /** @return ExternalIssue[] */
    public function findByProject(Project $project): array;

    /** @return ExternalIssue[] */
    public function findOpenByProject(Project $project): array;

    public function findByExternalId(Project $project, TrackerType $type, string $externalId): ?ExternalIssue;

    /**
     * @return ExternalIssue[]
     */
    public function findFiltered(Project $project, ?IssueStatus $status = null, ?string $search = null): array;

    public function save(ExternalIssue $issue, bool $flush = true): void;

    public function flush(): void;

    public function removeByProject(Project $project): void;
}
