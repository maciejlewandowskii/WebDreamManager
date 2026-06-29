<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker\Jira;

use App\Domain\IssueTracker\Application\Data\IssueData;
use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\IssueTracker\Enum\TrackerType;
use App\Domain\IssueTracker\Port\IssueTrackerClientInterface;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AutoconfigureTag('app.issue_tracker.client')]
final readonly class JiraIssueTrackerClient implements IssueTrackerClientInterface
{
    public function __construct(
        private HttpClientInterface               $httpClient,
        private SystemSettingRepositoryInterface $settings,
    ) {}

    public function supports(TrackerType $type): bool
    {
        return $type === TrackerType::Jira;
    }

    public function isConfigured(): bool
    {
        $token = $this->settings->get('JIRA_API_TOKEN');

        return $token !== null && $token !== '';
    }

    /** @return IssueData[] */
    public function fetchIssues(string $resource, array $options = []): array
    {
        $startAt = 0;
        $issues  = [];

        do {
            $response = $this->httpClient->request('GET', $this->baseUrl() . '/rest/api/3/search', [
                'headers' => $this->headers(),
                'query'   => [
                    'jql'        => "project=$resource ORDER BY created DESC",
                    'maxResults' => 100,
                    'startAt'    => $startAt,
                    'fields'     => 'summary,status,labels,assignee,issuetype',
                ],
            ]);

            $body    = $response->toArray();
            $batch   = $body['issues'] ?? [];
            $total   = $body['total'] ?? 0;

            foreach ($batch as $raw) {
                $issues[] = $this->mapIssue($raw);
            }

            $startAt += count($batch);
        } while ($startAt < $total && count($batch) > 0);

        return $issues;
    }

    public function fetchIssue(string $resource, string $issueId): ?IssueData
    {
        $response = $this->httpClient->request('GET', $this->baseUrl() . "/rest/api/3/issue/$issueId", [
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
        $response = $this->httpClient->request('POST', $this->baseUrl() . '/rest/api/3/issue', [
            'headers' => $this->headers(),
            'json'    => [
                'fields' => [
                    'project'     => ['key' => $resource],
                    'summary'     => $data['title'],
                    'description' => [
                        'type'    => 'doc',
                        'version' => 1,
                        'content' => [[
                            'type'    => 'paragraph',
                            'content' => [['type' => 'text', 'text' => $data['description']]],
                        ]],
                    ],
                    'issuetype'   => ['name' => 'Task'],
                ],
            ],
        ]);

        $created = $response->toArray();

        return $this->fetchIssue($resource, $created['key']) ?? new IssueData(
            externalId:  $created['id'],
            number:      null,
            title:       $data['title'],
            status:      IssueStatus::Open,
            url:         $this->baseUrl() . '/browse/' . $created['key'],
            assignee:    null,
            labels:      [],
            trackerType: TrackerType::Jira,
        );
    }

    private function baseUrl(): string
    {
        return rtrim((string) $this->settings->get('JIRA_BASE_URL'), '/');
    }

    /** @return array<string, string> */
    private function headers(): array
    {
        $email = (string) $this->settings->get('JIRA_EMAIL');
        $token = (string) $this->settings->get('JIRA_API_TOKEN');

        return [
            'Authorization' => 'Basic ' . base64_encode("$email:$token"),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /** @param array<string, mixed> $raw */
    private function mapIssue(array $raw): IssueData
    {
        $fields = $raw['fields'] ?? [];
        $key    = $raw['key'] ?? '';

        $categoryKey = $fields['status']['statusCategory']['key'] ?? 'new';
        $status = match ($categoryKey) {
            'indeterminate' => IssueStatus::InProgress,
            'done'          => IssueStatus::Closed,
            default         => IssueStatus::Open,
        };

        preg_match('/-(\d+)$/', $key, $m);
        $number = isset($m[1]) ? (int) $m[1] : null;

        return new IssueData(
            externalId:  $raw['id'] ?? $key,
            number:      $number,
            title:       $fields['summary'] ?? '',
            status:      $status,
            url:         $this->baseUrl() . '/browse/' . $key,
            assignee:    $fields['assignee']['displayName'] ?? null,
            labels:      $fields['labels'] ?? [],
            trackerType: TrackerType::Jira,
        );
    }
}
