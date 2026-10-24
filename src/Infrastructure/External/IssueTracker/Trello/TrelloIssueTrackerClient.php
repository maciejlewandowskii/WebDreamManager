<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker\Trello;

use App\Domain\IssueTracker\Application\Data\IssueData;
use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\IssueTracker\Enum\TrackerType;
use App\Domain\IssueTracker\Port\IssueTrackerClientInterface;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AutoconfigureTag('app.issue_tracker.client')]
final readonly class TrelloIssueTrackerClient implements IssueTrackerClientInterface
{
    private const BASE_URL = 'https://api.trello.com/1';

    public function __construct(
        private HttpClientInterface               $httpClient,
        private SystemSettingRepositoryInterface $settings,
    ) {}

    public function supports(TrackerType $type): bool
    {
        return $type === TrackerType::Trello;
    }

    public function isConfigured(): bool
    {
        $token = $this->settings->get('TRELLO_TOKEN');

        return $token !== null && $token !== '';
    }

    /** @return IssueData[] */
    public function fetchIssues(string $resource, array $options = []): array
    {
        $listsResponse = $this->httpClient->request('GET', self::BASE_URL . "/boards/$resource/lists", [
            'query' => $this->auth() + ['fields' => 'name,closed'],
        ]);

        $lists = $listsResponse->toArray();

        $issues = [];
        foreach ($lists as $list) {
            $cardsResponse = $this->httpClient->request('GET', self::BASE_URL . "/lists/{$list['id']}/cards", [
                'query' => $this->auth() + ['fields' => 'name,url,labels,idMembers,closed,idShort'],
            ]);

            foreach ($cardsResponse->toArray() as $card) {
                $issues[] = $this->mapCard($card, $list['name']);
            }
        }

        return $issues;
    }

    public function fetchIssue(string $resource, string $issueId): ?IssueData
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . "/cards/$issueId", [
            'query' => $this->auth() + ['fields' => 'name,url,labels,idMembers,closed,idShort,idList'],
        ]);

        if ($response->getStatusCode() === 404) {
            return null;
        }

        return $this->mapCard($response->toArray(), null);
    }

    /** @param array{title: string, description: string} $data */
    public function createIssue(string $resource, array $data): IssueData
    {
        $listsResponse = $this->httpClient->request('GET', self::BASE_URL . "/boards/$resource/lists", [
            'query' => $this->auth() + ['fields' => 'name', 'filter' => 'open'],
        ]);

        $lists  = $listsResponse->toArray();
        $listId = $lists[0]['id'] ?? null;

        $response = $this->httpClient->request('POST', self::BASE_URL . '/cards', [
            'query' => $this->auth() + [
                'idList' => $listId,
                'name'   => $data['title'],
                'desc'   => $data['description'],
            ],
        ]);

        return $this->mapCard($response->toArray(), null);
    }

    /** @return array<string, string> */
    private function auth(): array
    {
        return [
            'key'   => (string) $this->settings->get('TRELLO_API_KEY'),
            'token' => (string) $this->settings->get('TRELLO_TOKEN'),
        ];
    }

    /** @param array<string, mixed> $card */
    private function mapCard(array $card, ?string $listName): IssueData
    {
        $closed = $card['closed'] ?? false;
        $status = $closed ? IssueStatus::Closed : IssueStatus::Open;

        $labels = array_map(
            fn (array $l) => $l['name'] ?: $l['color'],
            $card['labels'] ?? [],
        );

        return new IssueData(
            externalId:  $card['id'] ?? '',
            number:      $card['idShort'] ?? null,
            title:       $card['name'] ?? '',
            status:      $status,
            url:         $card['url'] ?? null,
            assignee:    null,
            labels:      array_values(array_filter($labels)),
            trackerType: TrackerType::Trello,
        );
    }
}
