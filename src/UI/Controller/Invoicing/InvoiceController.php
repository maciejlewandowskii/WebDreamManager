<?php

declare(strict_types=1);

namespace App\UI\Controller\Invoicing;

use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/invoices', name: 'app_invoice_')]
final class InvoiceController extends AbstractController
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $repository,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('invoicing/invoice/index.html.twig', [
            'invoices' => $this->repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(): Response
    {
        return $this->render('invoicing/invoice/new.html.twig');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '[0-9a-f-]{36}'])]
    public function show(string $id): Response
    {
        $invoice = $this->repository->findById($id);

        if ($invoice === null) {
            throw $this->createNotFoundException('Invoice not found.');
        }

        return $this->render('invoicing/invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function delete(string $id, Request $request): Response
    {
        $invoice = $this->repository->findById($id);

        if ($invoice === null) {
            throw $this->createNotFoundException('Invoice not found.');
        }

        if ($this->isCsrfTokenValid('delete_invoice_' . $id, (string) $request->request->get('_token'))) {
            $this->repository->remove($invoice);
            $this->addFlash('success', 'Invoice deleted successfully.');
        }

        return $this->redirectToRoute('app_invoice_index');
    }
}
