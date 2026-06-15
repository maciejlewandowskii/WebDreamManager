<?php

declare(strict_types=1);

namespace App\UI\Controller\Admin;

use App\Domain\Logging\Entity\LogEntry;
use App\Domain\Logging\Repository\LogRepositoryInterface;
use App\UI\Controller\AppController;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('system.view')]
#[Route('/admin/logs', name: 'app_admin_log_')]
final class LogController extends AppController
{
    public function __construct(
        private readonly LogRepositoryInterface $repository,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('views/admin/logs/index.html.twig');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '[0-9a-f-]{36}'])]
    public function show(LogEntry $entry): Response
    {
        return $this->render('views/admin/logs/show.html.twig', [
            'entry' => $entry,
        ]);
    }

    #[Route('/purge', name: 'purge', methods: ['POST'])]
    #[IsGranted('system.manage')]
    public function purge(Request $request): Response
    {
        if ($this->isCsrfTokenValid('purge_logs', (string) $request->request->get('_token'))) {
            $days   = max(1, (int) $request->request->get('days', 30));
            $before = new DateTimeImmutable("-$days days");
            $count  = $this->repository->deleteOlderThan($before);
            $this->addFlash('success', "Purged $count log entries older than $days days.");
        }

        return $this->noContentResponse();
    }
}
