<?php

declare(strict_types=1);

namespace App\UI\Controller\Admin;

use App\Domain\Authorization\Entity\Permission;
use App\Domain\LinkShortener\Entity\ShortLink;
use App\Domain\LinkShortener\Repository\ShortLinkRepositoryInterface;
use App\UI\Controller\AppController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Permission::LinkShortenerView->value)]
#[Route('/admin/link-shortener', name: 'app_admin_link_shortener_')]
final class LinkShortenerController extends AppController
{
    public function __construct(private readonly ShortLinkRepositoryInterface $repository)
    {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('views/admin/link_shortener/index.html.twig');
    }

    #[IsGranted(Permission::LinkShortenerManage->value)]
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function delete(ShortLink $shortLink, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_short_link_' . $shortLink->getId(), (string) $request->request->get('_token'))) {
            $this->repository->remove($shortLink);

            return $this->noContentResponse('short_link:mutated');
        }

        return $this->noContentResponse();
    }
}
