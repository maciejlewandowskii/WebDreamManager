<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker\ClickUp;

use App\Domain\IssueTracker\Application\Data\IssueData;
use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\IssueTracker\Enum\TrackerType;
use App\Domain\IssueTracker\Port\IssueTrackerClientInterface;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AutoconfigureTag('app.issue_tracker.client')]
final readonly class ClickUpIssueTrackerClient implements IssueTrackerClientInterface
{
    private const string BASE_URL = 'https://api.clickup.com/api/v2';

    public function __construct(
        private HttpClientInterface               $httpClient,
        private SystemSettingRepositoryInterface $settings,
    ) {}

    public function supports(TrackerType $type): bool
    {
        return $type === TrackerType::ClickUp;
    }

    public function isConfigured(): bool
    {
        $token = $this->settings->get('CLICKUP_API_TOKEN');

        return $token !== null && $token !== '';
    }

    /** @return IssueData[] */
    public function fetchIssues(string $resource, array $options = []): array
    {
        $page   = 0;
        $issues = [];

        do {
            $response = $this->httpClient->request('GET', self::BASE_URL . "/list/$resource/task", [
                'headers' => $this->headers(),
                'query'   => ['page' => $page, 'include_closed' => 'true'],
            ]);

            $body  = $response->toArray();
            $tasks = $body['tasks'] ?? [];

            foreach ($tasks as $task) {
                $issues[] = $this->mapTask($task);
            }

            $page++;
        } while (!($body['last_page'] ?? true) && count($tasks) > 0);

        return $issues;
    }

    public function fetchIssue(string $resource, string $issueId): ?IssueData
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . "/task/$issueId", [
            'headers' => $this->headers(),
        ]);

        if ($response->getStatusCode() === 404) {
            return null;
        }

        return $this->mapTask($response->toArray());
    }

    /** @param array{title: string, description: string} $data */
    public function createIssue(string $resource, array $data): IssueData
    {
        $response = $this->httpClient->request('POST', self::BASE_URL . "/list/$resource/task", [
            'headers' => $this->headers(),
            'json'    => [
                'name'        => $data['title'],
                'description' => $data['description'],
            ],
        ]);

        return $this->mapTask($response->toArray());
    }

    /** @return array<string, string> */
    private function headers(): array
    {
        return [
            'Authorization' => (string) $this->settings->get('CLICKUP_API_TOKEN'),
            'Content-Type'  => 'application/json',
        ];
    }

    /** @param array<string, mixed> $task */
    private function mapTask(array $task): IssueData
    {
        $statusType = $task['status']['type'] ?? 'open';
        $status = match ($statusType) {
            'closed'       => IssueStatus::Closed,
            'in_progress'  => IssueStatus::InProgress,
            'done'         => IssueStatus::Resolved,
            default        => IssueStatus::Open,
        };

        $labels = array_map(
            static fn (array $t) => $t['name'],
            $task['tags'] ?? [],
        );

        $assignee = isset($task['assignees'][0]) ? $task['assignees'][0]['username'] : null;

        return new IssueData(
            externalId:  $task['id'] ?? '',
            number:      isset($task['id']) ? (int) base_convert($task['id'], 36, 10) : null,
            title:       $task['name'] ?? '',
            status:      $status,
            url:         $task['url'] ?? null,
            assignee:    $assignee,
            labels:      $labels,
            trackerType: TrackerType::ClickUp,
        );
    }
}
