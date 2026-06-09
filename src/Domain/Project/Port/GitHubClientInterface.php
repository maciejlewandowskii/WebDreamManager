<?php

declare(strict_types=1);

namespace App\Domain\Project\Port;

interface GitHubClientInterface
{
    /** @return array<int, array{id: int, number: int, title: string, state: string, url: string}> */
    public function getIssues(string $repository): array;

    /** @return array{id: int, number: int, title: string, state: string, url: string}|null */
    public function getIssue(string $repository, int $issueNumber): ?array;

    /** @return array<int, array{sha: string, message: string, author: string, date: string}> */
    public function getCommits(string $repository, int $limit = 20): array;
}
