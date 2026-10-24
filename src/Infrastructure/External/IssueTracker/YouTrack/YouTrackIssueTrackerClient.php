<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker\YouTrack;

use App\Domain\IssueTracker\Application\Data\IssueData;
use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\IssueTracker\Enum\TrackerType;
use App\Domain\IssueTracker\Port\IssueTrackerClientInterface;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AutoconfigureTag('app.issue_tracker.client')]
final readonly class YouTrackIssueTrackerClient implements IssueTrackerClientInterface
{
    public function __construct(
        private HttpClientInterface               $httpClient,
        private SystemSettingRepositoryInterface $settings,
    ) {}

    public function supports(TrackerType $type): bool
    {
        return $type === TrackerType::YouTrack;
    }

    public function isConfigured(): bool
    {
        $token = $this->settings->get('YOUTRACK_TOKEN');

        return $token !== null && $token !== '';
    }

    /** @return IssueData[] */
    public function fetchIssues(string $resource, array $options = []): array
    {
        $skip   = 0;
        $issues = [];

        do {
            $response = $this->httpClient->request('GET', $this->baseUrl() . '/api/issues', [
                'headers' => $this->headers(),
                'query'   => [
                    'query'  => "project: $resource",
                    'fields' => 'id,idReadable,summary,resolved,customFields(name,value(name)),tags(name),url',
                    '$top'   => 100,
                    '$skip'  => $skip,
                ],
            ]);

            $batch = $response->toArray();
            foreach ($batch as $issue) {
                $issues[] = $this->mapIssue($issue);
            }

            $skip += count($batch);
        } while (count($batch) === 100);

        return $issues;
    }

    public function fetchIssue(string $resource, string $issueId): ?IssueData
    {
        $response = $this->httpClient->request('GET', $this->baseUrl() . "/api/issues/$issueId", [
            'headers' => $this->headers(),
            'query'   => ['fields' => 'id,idReadable,summary,resolved,customFields(name,value(name)),tags(name),url'],
        ]);

        if ($response->getStatusCode() === 404) {
            return null;
        }

        return $this->mapIssue($response->toArray());
    }

    /** @param array{title: string, description: string} $data */
    public function createIssue(string $resource, array $data): IssueData
    {
        $response = $this->httpClient->request('POST', $this->baseUrl() . '/api/issues', [
            'headers' => $this->headers(),
            'json'    => [
                'project'     => ['id' => $resource],
                'summary'     => $data['title'],
                'description' => $data['description'],
            ],
            'query' => ['fields' => 'id,idReadable,summary,resolved,url'],
        ]);

        return $this->mapIssue($response->toArray());
    }

    private function baseUrl(): string
    {
        return rtrim((string) $this->settings->get('YOUTRACK_BASE_URL'), '/');
    }

    /** @return array<string, string> */
    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->settings->get('YOUTRACK_TOKEN'),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /** @param array<string, mixed> $raw */
    private function mapIssue(array $raw): IssueData
    {
        $resolved = $raw['resolved'] ?? null;
        $state    = null;

        foreach ($raw['customFields'] ?? [] as $field) {
            if ($field['name'] === 'State') {
                $state = $field['value']['name'] ?? null;
                break;
            }
        }

        $status = match (true) {
            $resolved !== null                                         => IssueStatus::Resolved,
            $state !== null && str_contains(strtolower($state), 'progress') => IssueStatus::InProgress,
            $state !== null && in_array(strtolower($state), ['fixed', 'closed', 'won\'t fix', 'obsolete'], true) => IssueStatus::Closed,
            default                                                    => IssueStatus::Open,
        };

        $labels = array_map(static fn (array $t) => $t['name'], $raw['tags'] ?? []);

        preg_match('/-(\d+)$/', $raw['idReadable'] ?? '', $m);
        $number = isset($m[1]) ? (int) $m[1] : null;

        return new IssueData(
            externalId:  $raw['id'] ?? '',
            number:      $number,
            title:       $raw['summary'] ?? '',
            status:      $status,
            url:         isset($raw['idReadable']) ? $this->baseUrl() . '/issue/' . $raw['idReadable'] : null,
            assignee:    null,
            labels:      $labels,
            trackerType: TrackerType::YouTrack,
        );
    }
}
