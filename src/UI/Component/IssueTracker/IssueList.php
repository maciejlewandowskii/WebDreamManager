<?php

declare(strict_types=1);

namespace App\UI\Component\IssueTracker;

use App\Domain\IssueTracker\Application\IssueTrackerService;
use App\Domain\IssueTracker\Entity\ExternalIssue;
use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('IssueList', template: 'components/issue_tracker/IssueList.html.twig')]
final class IssueList
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $projectId = '';

    #[LiveProp(writable: true)]
    public string $filterStatus = 'open';

    #[LiveProp(writable: true)]
    public string $search = '';

    #[LiveProp]
    public int $limit = 0;

    public int $syncCount = 0;

    public bool $synced = false;

    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly IssueTrackerService        $service,
    ) {}

    /** @return ExternalIssue[] */
    public function getIssues(): array
    {
        $project = $this->projects->findById($this->projectId);
        if ($project === null) {
            return [];
        }

        $status  = $this->filterStatus !== 'all' ? IssueStatus::tryFrom($this->filterStatus) : null;
        $search  = $this->search !== '' ? $this->search : null;
        $issues  = $this->service->getCachedIssues($project);

        if ($status !== null) {
            $issues = array_filter($issues, fn (ExternalIssue $i) => $i->getStatus() === $status);
        }

        if ($search !== null) {
            $issues = array_filter($issues, fn (ExternalIssue $i) => str_contains(
                mb_strtolower($i->getTitle()),
                mb_strtolower($search),
            ));
        }

        $issues = array_values($issues);

        if ($this->limit > 0) {
            $issues = array_slice($issues, 0, $this->limit);
        }

        return $issues;
    }

    #[LiveAction]
    public function sync(): void
    {
        $project = $this->projects->findById($this->projectId);
        if ($project === null) {
            return;
        }

        $this->syncCount = $this->service->syncIssues($project);
        $this->synced    = true;
    }
}
