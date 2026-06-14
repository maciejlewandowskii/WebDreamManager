<?php

declare(strict_types=1);

namespace App\UI\Controller\Admin;

use App\Domain\Authorization\Application\Data\RoleData;
use App\Domain\Authorization\Application\Pipeline\CreateRole\CreateRoleCommand;
use App\Domain\Authorization\Entity\Permission as AuthPermission;
use App\Domain\Authorization\Application\Pipeline\DeleteRole\DeleteRoleCommand;
use App\Domain\Authorization\Application\Pipeline\UpdateRole\UpdateRoleCommand;
use App\Domain\Authorization\Entity\Permission;
use App\Domain\Authorization\Entity\Role;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use App\Infrastructure\Pipeline\PipelineProcessor;
use App\UI\Controller\AppController;
use App\UI\Form\Admin\RoleType;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Permission::RoleList->value)]
#[Route('/admin/roles', name: 'app_admin_role_')]
final class RoleController extends AppController
{
    /**
     * @param iterable<PipelineHandlerInterface> $createHandlers
     * @param iterable<PipelineHandlerInterface> $updateHandlers
     * @param iterable<PipelineHandlerInterface> $deleteHandlers
     */
    public function __construct(
        #[AutowireIterator('app.authorization.role.create')] private readonly iterable $createHandlers,
        #[AutowireIterator('app.authorization.role.update')] private readonly iterable $updateHandlers,
        #[AutowireIterator('app.authorization.role.delete')] private readonly iterable $deleteHandlers,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('views/admin/roles/index.html.twig');
    }

    #[IsGranted(Permission::RoleCreate->value)]
    #[Route('/new', name: 'new', condition: "request.isMethod('POST') or request.headers.get('Turbo-Frame')")]
    public function new(Request $request): Response
    {
        $data = new RoleData();
        $form = $this->createForm(RoleType::class, $data, [
            'action' => $this->generateUrl('app_admin_role_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = new CreateRoleCommand($data);
            new PipelineProcessor($this->createHandlers)->run($command);
            $this->addFlash('success', 'Role created.');

            return $this->noContentResponse('role:mutated');
        }

        return $this->render('views/admin/roles/new.html.twig', [
            'form'             => $form,
            'permissionGroups' => AuthPermission::groupedByResource(),
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[IsGranted(Permission::RoleUpdate->value)]
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '[0-9a-f-]{36}'], condition: "request.isMethod('POST') or request.headers.get('Turbo-Frame')")]
    public function edit(Role $role, Request $request): Response
    {
        $data = RoleData::fromRole($role);
        $form = $this->createForm(RoleType::class, $data, [
            'action' => $this->generateUrl('app_admin_role_edit', ['id' => $role->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            new PipelineProcessor($this->updateHandlers)->run(new UpdateRoleCommand($role, $data));

            $this->addFlash('success', 'Role updated.');

            return $this->noContentResponse('role:mutated');
        }

        return $this->render('views/admin/roles/edit.html.twig', [
            'form'             => $form,
            'role'             => $role,
            'permissionGroups' => AuthPermission::groupedByResource(),
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[IsGranted(Permission::RoleDelete->value)]
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '[0-9a-f-]{36}'], methods: ['POST'])]
    public function delete(Role $role, Request $request): Response
    {
        if ($role->isSystem()) {
            $this->addFlash('error', 'System roles cannot be deleted.');

            return $this->noContentResponse('', Response::HTTP_FORBIDDEN);
        }

        if ($this->isCsrfTokenValid('delete_role_' . $role->getId(), (string) $request->request->get('_token'))) {
            new PipelineProcessor($this->deleteHandlers)->run(new DeleteRoleCommand($role));

            $this->addFlash('success', 'Role deleted.');

            return $this->noContentResponse('role:mutated');
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
