<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker\Trello;

use App\Domain\IssueTracker\Application\Data\IssueData;
use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\IssueTracker\Enum\TrackerType;
use App\Domain\IssueTracker\Port\IssueTrackerClientInterface;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.issue_tracker.client')]
final class TrelloIssueTrackerClient implements IssueTrackerClientInterface
{
    public function __construct(
        private readonly SystemSettingRepositoryInterface $settings,
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

    public function fetchIssues(string $resource, array $options = []): array
    {
        // TODO: implement Trello REST API integration
        return [];
    }

    public function fetchIssue(string $resource, string $issueId): ?IssueData
    {
        // TODO: implement Trello REST API integration
        return null;
    }

    public function createIssue(string $resource, array $data): IssueData
    {
        // TODO: implement Trello REST API integration
        return new IssueData('', null, $data['title'], IssueStatus::Open, null, null, [], TrackerType::Trello);
    }
}
