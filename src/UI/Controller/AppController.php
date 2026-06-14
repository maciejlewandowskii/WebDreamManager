<?php

declare(strict_types=1);

namespace App\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

abstract class AppController extends AbstractController
{
    protected function noContentResponse(string $dispatchEvent = '', int $status = Response::HTTP_OK): Response
    {
        $headers = array_filter([
            'Content-Type'     => 'text/html',
            'X-Dispatch-Event' => $dispatchEvent ?: null,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if ($request !== null && $request->hasSession()) {
            $session = $request->getSession();
            if ($session instanceof FlashBagAwareSessionInterface) {
                $flashes = $session->getFlashBag()->all();
                if ($flashes !== []) {
                    /** @noinspection JsonEncodingApiUsageInspection */
                    $headers['X-Flash-Messages'] = json_encode($flashes);
                }
            }
        }

        return new Response('<turbo-frame id="modal"></turbo-frame>', $status, $headers);
    }

    /** @param array<string, mixed> $params */
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
