<?php

declare(strict_types=1);

namespace App\Domain\IssueTracker\Application;

use App\Domain\IssueTracker\Entity\ExternalIssue;
use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\IssueTracker\Port\ExternalIssueRepositoryInterface;
use App\Domain\IssueTracker\Port\IssueTrackerClientInterface;
use App\Domain\Invoicing\Entity\Quote;
use App\Domain\Project\Entity\Project;
use App\Infrastructure\External\IssueTracker\IssueTrackerRegistry;

final readonly class IssueTrackerService
{
    public function __construct(
        private IssueTrackerRegistry            $registry,
        private ExternalIssueRepositoryInterface $repository,
    ) {}

    public function hasTracker(Project $project): bool
    {
        return $project->hasTracker();
    }

    public function getClient(Project $project): ?IssueTrackerClientInterface
    {
        return $this->registry->getConfiguredClientForProject($project);
    }

    public function syncIssues(Project $project): int
    {
        $client = $this->registry->getConfiguredClientForProject($project);
        if ($client === null || $project->getTrackerResource() === null) {
            return 0;
        }

        $fetched = $client->fetchIssues($project->getTrackerResource());
        $count   = 0;

        foreach ($fetched as $issueData) {
            $existing = $this->repository->findByExternalId(
                $project,
                $project->getTrackerType(),
                $issueData->externalId,
            );

            if ($existing === null) {
                $issue = new ExternalIssue(
                    $project,
                    $project->getTrackerType(),
                    $issueData->externalId,
                    $issueData->title,
                );
            } else {
                $issue = $existing;
                $issue->setTitle($issueData->title);
                $issue->touch();
            }

            $issue->setStatus($issueData->status);
            $issue->setExternalNumber($issueData->number);
            $issue->setUrl($issueData->url);
            $issue->setAssignee($issueData->assignee);
            $issue->setLabels($issueData->labels ?: null);

            $this->repository->save($issue, flush: false);
            $count++;
        }

        $this->repository->flush();

        return $count;
    }

    /** @return ExternalIssue[] */
    public function getCachedIssues(Project $project): array
    {
        return $this->repository->findByProject($project);
    }

    /** @return ExternalIssue[] */
    public function getCachedOpenIssues(Project $project): array
    {
        return $this->repository->findOpenByProject($project);
    }

    public function createIssuesFromQuote(Quote $quote, Project $project): int
    {
        $client = $this->registry->getConfiguredClientForProject($project);
        if ($client === null || $project->getTrackerResource() === null) {
            return 0;
        }

        $count = 0;
        foreach ($quote->getItems() as $item) {
            $issueData = $client->createIssue($project->getTrackerResource(), [
                'title'       => $item->getDescription(),
                'description' => sprintf(
                    'From quote %s — %s %s @ %s',
                    $quote->getNumber(),
                    $item->getQuantity(),
                    $item->getUnit(),
                    $item->getUnitPrice(),
                ),
            ]);

            $issue = new ExternalIssue(
                $project,
                $project->getTrackerType(),
                $issueData->externalId,
                $issueData->title,
            );
            $issue->setStatus(IssueStatus::Open);
            $issue->setUrl($issueData->url);

            $this->repository->save($issue, flush: false);
            $count++;
        }

        $this->repository->flush();

        return $count;
    }
}
