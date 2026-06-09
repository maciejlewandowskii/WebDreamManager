<?php

declare(strict_types=1);

namespace App\Infrastructure\External\GitHub;

use App\Domain\Project\Port\GitHubClientInterface;

final class StubGitHubClient implements GitHubClientInterface
{
    public function getIssues(string $repository): array
    {
        return [];
    }

    public function getIssue(string $repository, int $issueNumber): ?array
    {
        return null;
    }

    public function getCommits(string $repository, int $limit = 20): array
    {
        return [];
    }
}
