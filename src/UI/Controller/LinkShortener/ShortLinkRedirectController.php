<?php

declare(strict_types=1);

namespace App\UI\Controller\LinkShortener;

use App\Domain\LinkShortener\Repository\ShortLinkRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ShortLinkRedirectController extends AbstractController
{
    public function __construct(private readonly ShortLinkRepositoryInterface $repository)
    {
    }

    #[Route('/s/{code}', name: 'app_short_link_redirect', requirements: ['code' => '[A-Za-z0-9]{4,12}'])]
    public function __invoke(string $code): RedirectResponse
    {
        $shortLink = $this->repository->findByCode($code);

        if ($shortLink === null) {
            throw $this->createNotFoundException('Link not found.');
        }

        $shortLink->recordClick();
        $this->repository->save($shortLink);

        return new RedirectResponse($shortLink->getTargetUrl());
    }
}
