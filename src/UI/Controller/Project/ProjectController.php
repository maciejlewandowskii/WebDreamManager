<?php

declare(strict_types=1);

namespace App\UI\Controller\Project;

use App\Domain\Authorization\Application\Data\ProjectMemberData;
use App\Domain\Authorization\Application\Pipeline\AddProjectMember\AddProjectMemberCommand;
use App\Domain\Authorization\Application\Pipeline\RemoveProjectMember\RemoveProjectMemberCommand;
use App\Domain\Authorization\Application\Pipeline\UpdateProjectMember\UpdateProjectMemberCommand;
use App\Domain\Authorization\Entity\Permission;
use App\Domain\Authorization\Repository\ProjectMemberRepositoryInterface;
use App\Domain\Project\Application\Data\ProjectData;
use App\Domain\Project\Application\Pipeline\CreateProject\CreateProjectCommand;
use App\Domain\Project\Application\Pipeline\DeleteProject\DeleteProjectCommand;
use App\Domain\Project\Application\Pipeline\UpdateProject\UpdateProjectCommand;
use App\Domain\Project\Entity\Project;
use App\Infrastructure\Pipeline\PipelineProcessor;
use App\UI\Controller\AppController;
use App\UI\Form\Admin\ProjectMemberType;
use App\UI\Form\ProjectType;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/projects', name: 'app_project_')]
final class ProjectController extends AppController
{
    public function __construct(
        #[AutowireIterator('app.project.create')] private readonly iterable $createHandlers,
        #[AutowireIterator('app.project.update')] private readonly iterable $updateHandlers,
        #[AutowireIterator('app.project.delete')] private readonly iterable $deleteHandlers,
        #[AutowireIterator('app.authorization.project_member.add')] private readonly iterable $memberAddHandlers,
        #[AutowireIterator('app.authorization.project_member.update')] private readonly iterable $memberUpdateHandlers,
        #[AutowireIterator('app.authorization.project_member.remove')] private readonly iterable $memberRemoveHandlers,
        private readonly ProjectMemberRepositoryInterface $memberRepository,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('views/project/index.html.twig');
    }

    #[Route('/new', name: 'new', condition: "request.isMethod('POST') or request.headers.get('Turbo-Frame')")]
    public function new(Request $request): Response
    {
        $data = new ProjectData();
        $form = $this->createForm(ProjectType::class, $data, [
            'action' => $this->generateUrl('app_project_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = CreateProjectCommand::fromData($data);
            new PipelineProcessor($this->createHandlers)->run($command);
            $this->addFlash('success', 'Project created successfully.');

            return $this->redirectToRoute('app_project_show', ['id' => $command->result->getId()]);
        }

        return $this->render('views/project/new.html.twig', [
            'form' => $form,
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '[0-9a-f-]{36}'], condition: "request.isMethod('POST') or request.headers.get('Turbo-Frame')")]
    public function edit(Project $project, Request $request): Response
    {
        $data = ProjectData::fromEntity($project);
        $form = $this->createForm(ProjectType::class, $data, [
            'action' => $this->generateUrl('app_project_edit', ['id' => $project->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            new PipelineProcessor($this->updateHandlers)->run(new UpdateProjectCommand($project, $data));
            $this->addFlash('success', 'Project updated successfully.');

            return $this->redirectToReferer($request, 'app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('views/project/edit.html.twig', [
            'form'    => $form,
            'project' => $project,
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '[0-9a-f-]{36}'])]
    public function show(Project $project): Response
    {
        return $this->render('views/project/show.html.twig', [
            'project' => $project,
            'members' => $this->memberRepository->findByProject($project),
        ]);
    }

    #[Route('/{id}/manage-files', name: 'files', requirements: ['id' => '[0-9a-f-]{36}'])]
    public function files(Project $project): Response
    {
        return $this->render('views/project/files.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '[0-9a-f-]{36}'], methods: ['POST'])]
    public function delete(Project $project, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_project_' . $project->getId(), (string) $request->request->get('_token'))) {
            new PipelineProcessor($this->deleteHandlers)->run(new DeleteProjectCommand($project));
            $this->addFlash('success', 'Project deleted successfully.');
        }

        return $this->redirectToReferer($request, 'app_project_index');
    }

    #[IsGranted(Permission::ProjectAssignMembers->value, subject: 'project')]
    #[Route('/{id}/members/add', name: 'member_add', requirements: ['id' => '[0-9a-f-]{36}'], condition: "request.isMethod('POST') or request.headers.get('Turbo-Frame')")]
    public function addMember(Project $project, Request $request): Response
    {
        $data = new ProjectMemberData();
        $form = $this->createForm(ProjectMemberType::class, $data, [
            'action' => $this->generateUrl('app_project_member_add', ['id' => $project->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $this->memberRepository->findByUserAndProject($data->user, $project);

            if ($existing !== null) {
                new PipelineProcessor($this->memberUpdateHandlers)->run(
                    new UpdateProjectMemberCommand($existing, $data),
                );
            } else {
                new PipelineProcessor($this->memberAddHandlers)->run(
                    new AddProjectMemberCommand($data, $project),
                );
            }

            $this->addFlash('success', 'Member added to project.');

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('views/project/member_form.html.twig', [
            'form'    => $form,
            'project' => $project,
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[IsGranted(Permission::ProjectAssignMembers->value, subject: 'project')]
    #[Route('/{id}/members/{memberId}/remove', name: 'member_remove', requirements: ['id' => '[0-9a-f-]{36}', 'memberId' => '[0-9a-f-]{36}'], methods: ['POST'])]
    public function removeMember(Project $project, string $memberId, Request $request): Response
    {
        $member = $this->memberRepository->findById($memberId);

        if ($member !== null
            && $member->getProject()->getId() === $project->getId()
            && $this->isCsrfTokenValid('remove_member_' . $memberId, (string) $request->request->get('_token'))
        ) {
            new PipelineProcessor($this->memberRemoveHandlers)->run(new RemoveProjectMemberCommand($member));
            $this->addFlash('success', 'Member removed from project.');
        }

        return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
    }
}
