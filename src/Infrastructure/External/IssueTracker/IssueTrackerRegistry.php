<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker;

use App\Domain\IssueTracker\Enum\TrackerType;
use App\Domain\IssueTracker\Port\IssueTrackerClientInterface;
use App\Domain\Project\Entity\Project;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class IssueTrackerRegistry
{
    /** @var IssueTrackerClientInterface[] */
    private array $clients;

    /**
     * @param iterable<IssueTrackerClientInterface> $clients
     */
    public function __construct(
        #[AutowireIterator('app.issue_tracker.client')]
        iterable $clients,
    ) {
        $this->clients = iterator_to_array($clients);
    }

    public function getClient(TrackerType $type): ?IssueTrackerClientInterface
    {
        foreach ($this->clients as $client) {
            if ($client->supports($type)) {
                return $client;
            }
        }

        return null;
    }

    public function getConfiguredClientForProject(Project $project): ?IssueTrackerClientInterface
    {
        if (!$project->hasTracker()) {
            return null;
        }

        $client = $this->getClient($project->getTrackerType());

        return ($client !== null && $client->isConfigured()) ? $client : null;
    }
}
