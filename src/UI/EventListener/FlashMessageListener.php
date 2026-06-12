<?php

declare(strict_types=1);

namespace App\UI\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class FlashMessageListener
{
    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        // If it is a redirect, do not extract flashes, so they carry over to the next request
        if ($response->isRedirect()) {
            return;
        }

        // Check if this is a Turbo or AJAX request
        $isTurbo = $request->headers->has('Turbo-Frame')
            || $request->headers->has('X-Turbo-Request')
            || str_contains($request->headers->get('Accept', ''), 'text/vnd.turbo-stream.html')
            || $request->headers->get('X-Requested-With') === 'XMLHttpRequest';

        if (!$isTurbo) {
            return;
        }

        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();
        
        // Ensure getFlashBag exists (it does on Symfony Session)
        if (method_exists($session, 'getFlashBag')) {
            $flashBag = $session->getFlashBag();
            $flashes = $flashBag->all();

            if (!empty($flashes)) {
                $response->headers->set('X-Flash-Messages', json_encode($flashes, JSON_THROW_ON_ERROR));
            }
        }
    }
}
