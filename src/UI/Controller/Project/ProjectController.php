<?php

declare(strict_types=1);

namespace App\UI\Controller\Project;

use App\Domain\Project\Repository\ProjectRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/projects', name: 'app_project_')]
final class ProjectController extends AbstractController
{
    public function __construct(
        private readonly ProjectRepositoryInterface $repository,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('project/index.html.twig', [
            'projects' => $this->repository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '[0-9a-f-]{36}'])]
    public function show(string $id): Response
    {
        $project = $this->repository->findById($id);

        if ($project === null) {
            throw $this->createNotFoundException('Project not found.');
        }

        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function delete(string $id, Request $request): Response
    {
        $project = $this->repository->findById($id);

        if ($project === null) {
            throw $this->createNotFoundException('Project not found.');
        }

        if ($this->isCsrfTokenValid('delete_project_' . $id, (string) $request->request->get('_token'))) {
            $this->repository->remove($project);
            $this->addFlash('success', 'Project deleted successfully.');
        }

        return $this->redirectToRoute('app_project_index');
    }
}
