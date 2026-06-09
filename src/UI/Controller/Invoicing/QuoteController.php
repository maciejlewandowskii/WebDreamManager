<?php

declare(strict_types=1);

namespace App\UI\Controller\Invoicing;

use App\Domain\Invoicing\Repository\QuoteRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/quotes', name: 'app_quote_')]
final class QuoteController extends AbstractController
{
    public function __construct(
        private readonly QuoteRepositoryInterface $repository,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('invoicing/quote/index.html.twig', [
            'quotes' => $this->repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(): Response
    {
        return $this->render('invoicing/quote/new.html.twig');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '[0-9a-f-]{36}'])]
    public function show(string $id): Response
    {
        $quote = $this->repository->findById($id);

        if ($quote === null) {
            throw $this->createNotFoundException('Quote not found.');
        }

        return $this->render('invoicing/quote/show.html.twig', [
            'quote' => $quote,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function delete(string $id, Request $request): Response
    {
        $quote = $this->repository->findById($id);

        if ($quote === null) {
            throw $this->createNotFoundException('Quote not found.');
        }

        if ($this->isCsrfTokenValid('delete_quote_' . $id, (string) $request->request->get('_token'))) {
            $this->repository->remove($quote);
            $this->addFlash('success', 'Quote deleted successfully.');
        }

        return $this->redirectToRoute('app_quote_index');
    }
}
