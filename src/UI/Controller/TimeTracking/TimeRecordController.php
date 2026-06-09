<?php

declare(strict_types=1);

namespace App\UI\Controller\TimeTracking;

use App\Domain\TimeTracking\Repository\TimeRecordRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/time', name: 'app_time_')]
final class TimeRecordController extends AbstractController
{
    public function __construct(
        private readonly TimeRecordRepositoryInterface $repository,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('time_tracking/index.html.twig', [
            'records' => $this->repository->findAll(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function delete(string $id, Request $request): Response
    {
        $record = $this->repository->findById($id);

        if ($record === null) {
            throw $this->createNotFoundException('Time record not found.');
        }

        if ($this->isCsrfTokenValid('delete_time_' . $id, (string) $request->request->get('_token'))) {
            $this->repository->remove($record);
            $this->addFlash('success', 'Time record deleted.');
        }

        return $this->redirectToRoute('app_time_index');
    }
}
