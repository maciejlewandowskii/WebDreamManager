<?php

declare(strict_types=1);

namespace App\UI\Controller\Project;

use App\Domain\IssueTracker\Application\IssueTrackerService;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IssueTrackerController extends AbstractController
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly IssueTrackerService        $service,
    ) {}

    #[Route('/projects/{id}/issues', name: 'app_project_issues', methods: ['GET'])]
    public function issues(string $id): Response
    {
        $project = $this->projects->findById($id);
        if ($project === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('views/project/issues.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/projects/{id}/issues/sync', name: 'app_project_issues_sync', methods: ['POST'])]
    public function sync(string $id): Response
    {
        $project = $this->projects->findById($id);
        if ($project === null) {
            throw $this->createNotFoundException();
        }

        $count = $this->service->syncIssues($project);
        $this->addFlash('success', "Synced {$count} issues from {$project->getTrackerType()->label()}.");

        return $this->redirectToRoute('app_project_issues', ['id' => $id]);
    }
}
