<?php

declare(strict_types=1);

namespace App\UI\Controller\Admin;

use App\Domain\Authorization\Application\Data\UserAdminData;
use App\Domain\Authorization\Application\Pipeline\CreateUser\CreateUserCommand;
use App\Domain\Authorization\Application\Pipeline\DeleteUser\DeleteUserCommand;
use App\Domain\Authorization\Application\Pipeline\UpdateUser\UpdateUserCommand;
use App\Domain\Authorization\Entity\Permission;
use App\Domain\Identity\Entity\User;
use App\Infrastructure\Pipeline\PipelineProcessor;
use App\UI\Controller\AppController;
use App\UI\Form\Admin\UserManageType;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Permission::UserList->value)]
#[Route('/admin/users', name: 'app_admin_user_')]
final class UserController extends AppController
{
    public function __construct(
        #[AutowireIterator('app.authorization.user.create')] private readonly iterable $createHandlers,
        #[AutowireIterator('app.authorization.user.update')] private readonly iterable $updateHandlers,
        #[AutowireIterator('app.authorization.user.delete')] private readonly iterable $deleteHandlers,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('views/admin/users/index.html.twig');
    }

    #[IsGranted(Permission::UserCreate->value)]
    #[Route('/new', name: 'new', condition: "request.isMethod('POST') or request.headers.get('Turbo-Frame')")]
    public function new(Request $request): Response
    {
        $data = new UserAdminData();
        $form = $this->createForm(UserManageType::class, $data, [
            'action' => $this->generateUrl('app_admin_user_new'),
            'is_new' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = new CreateUserCommand($data);
            new PipelineProcessor($this->createHandlers)->run($command);
            $this->addFlash('success', 'User created.');

            return $this->noContentResponse('user:mutated');
        }

        return $this->render('views/admin/users/new.html.twig', [
            'form' => $form,
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[IsGranted(Permission::UserUpdate->value)]
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '[0-9a-f-]{36}'], condition: "request.isMethod('POST') or request.headers.get('Turbo-Frame')")]
    public function edit(User $user, Request $request): Response
    {
        $data = UserAdminData::fromUser($user);
        $form = $this->createForm(UserManageType::class, $data, [
            'action' => $this->generateUrl('app_admin_user_edit', ['id' => $user->getId()]),
            'is_new' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            new PipelineProcessor($this->updateHandlers)->run(
                new UpdateUserCommand($user, $data, $plain !== '' ? $plain : null),
            );

            $this->addFlash('success', 'User updated.');

            return $this->noContentResponse('user:mutated');
        }

        return $this->render('views/admin/users/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[IsGranted(Permission::UserDelete->value)]
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '[0-9a-f-]{36}'], methods: ['POST'])]
    public function delete(User $user, Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($user->getId() === $currentUser->getId()) {
            $this->addFlash('error', 'You cannot delete your own account.');

            return $this->noContentResponse('', Response::HTTP_FORBIDDEN);
        }

        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), (string) $request->request->get('_token'))) {
            new PipelineProcessor($this->deleteHandlers)->run(new DeleteUserCommand($user));

            $this->addFlash('success', 'User deleted.');

            return $this->noContentResponse('user:mutated');
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
