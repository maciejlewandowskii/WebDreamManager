<?php

declare(strict_types=1);

namespace App\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AppController extends AbstractController
{
    protected function redirectToReferer(Request $request, string $fallbackRoute, array $params = []): RedirectResponse
    {
        $referer = $request->headers->get('referer');

        if (
            $referer !== null &&
            $referer !== '' &&
            (str_starts_with($referer, '/') || str_starts_with($referer, $request->getSchemeAndHttpHost()))
        ) {
            return new RedirectResponse($referer);
        }

        return $this->redirectToRoute($fallbackRoute, $params);
    }
}
