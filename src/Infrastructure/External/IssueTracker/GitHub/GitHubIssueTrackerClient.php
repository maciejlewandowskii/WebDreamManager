<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker\GitHub;

use App\Domain\IssueTracker\Application\Data\IssueData;
use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\IssueTracker\Enum\TrackerType;
use App\Domain\IssueTracker\Port\IssueTrackerClientInterface;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AutoconfigureTag('app.issue_tracker.client')]
final class GitHubIssueTrackerClient implements IssueTrackerClientInterface
{
    private const BASE_URL = 'https://api.github.com';

    public function __construct(
        private readonly HttpClientInterface              $httpClient,
        private readonly SystemSettingRepositoryInterface $settings,
    ) {}

    public function supports(TrackerType $type): bool
    {
        return $type === TrackerType::GitHub;
    }

    public function isConfigured(): bool
    {
        $token = $this->settings->get('GITHUB_TOKEN');

        return $token !== null && $token !== '';
    }

    /** @return IssueData[] */
    public function fetchIssues(string $resource, array $options = []): array
    {
        $baseUrl = $this->settings->get('GITHUB_API_URL') ?: self::BASE_URL;
        $state   = $options['state'] ?? 'open';
        $page    = 1;
        $issues  = [];

        do {
            $response = $this->httpClient->request('GET', "{$baseUrl}/repos/{$resource}/issues", [
                'headers' => $this->headers(),
                'query'   => ['state' => $state, 'per_page' => 100, 'page' => $page],
            ]);

            $batch = $response->toArray();
            foreach ($batch as $raw) {
                if (isset($raw['pull_request'])) {
                    continue;
                }
                $issues[] = $this->mapIssue($raw);
            }

            $linkHeader = $response->getHeaders()['link'][0] ?? '';
            $hasNext    = str_contains($linkHeader, 'rel="next"');
            $page++;
        } while ($hasNext && count($batch) > 0);

        return $issues;
    }

    public function fetchIssue(string $resource, string $issueId): ?IssueData
    {
        $baseUrl  = $this->settings->get('GITHUB_API_URL') ?: self::BASE_URL;
        $response = $this->httpClient->request('GET', "{$baseUrl}/repos/{$resource}/issues/{$issueId}", [
            'headers' => $this->headers(),
        ]);

        if ($response->getStatusCode() === 404) {
            return null;
        }

        return $this->mapIssue($response->toArray());
    }

    /** @param array{title: string, description: string} $data */
    public function createIssue(string $resource, array $data): IssueData
    {
        $baseUrl  = $this->settings->get('GITHUB_API_URL') ?: self::BASE_URL;
        $response = $this->httpClient->request('POST', "{$baseUrl}/repos/{$resource}/issues", [
            'headers' => $this->headers(),
            'json'    => ['title' => $data['title'], 'body' => $data['description']],
        ]);

        return $this->mapIssue($response->toArray());
    }

    /** @return array<string, string> */
    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->settings->get('GITHUB_TOKEN'),
            'Accept'        => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ];
    }

    /** @param array<string, mixed> $raw */
    private function mapIssue(array $raw): IssueData
    {
        $status = match ($raw['state'] ?? 'open') {
            'closed' => IssueStatus::Closed,
            default  => IssueStatus::Open,
        };

        $labels = array_map(
            fn (array $l) => $l['name'],
            $raw['labels'] ?? [],
        );

        return new IssueData(
            externalId:  (string) $raw['id'],
            number:      $raw['number'] ?? null,
            title:       $raw['title'] ?? '',
            status:      $status,
            url:         $raw['html_url'] ?? null,
            assignee:    $raw['assignee']['login'] ?? null,
            labels:      $labels,
            trackerType: TrackerType::GitHub,
        );
    }
}
