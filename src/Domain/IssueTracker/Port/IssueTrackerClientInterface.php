<?php

declare(strict_types=1);

namespace App\Domain\IssueTracker\Port;

use App\Domain\IssueTracker\Application\Data\IssueData;
use App\Domain\IssueTracker\Enum\TrackerType;

interface IssueTrackerClientInterface
{
    public function supports(TrackerType $type): bool;

    public function isConfigured(): bool;

    /**
     * @param  array<string, mixed> $options  e.g. ['state' => 'open']
     * @return IssueData[]
     */
    public function fetchIssues(string $resource, array $options = []): array;

    public function fetchIssue(string $resource, string $issueId): ?IssueData;

    /**
     * @param array{title: string, description: string} $data
     */
    public function createIssue(string $resource, array $data): IssueData;
}
