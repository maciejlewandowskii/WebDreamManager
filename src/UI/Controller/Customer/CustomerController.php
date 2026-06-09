<?php

declare(strict_types=1);

namespace App\UI\Controller\Customer;

use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/customers', name: 'app_customer_')]
final class CustomerController extends AbstractController
{
    public function __construct(
        private readonly CustomerRepositoryInterface $repository,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('customer/index.html.twig', [
            'customers' => $this->repository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '[0-9a-f-]{36}'])]
    public function show(string $id): Response
    {
        $customer = $this->repository->findById($id);

        if ($customer === null) {
            throw $this->createNotFoundException('Customer not found.');
        }

        return $this->render('customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function delete(string $id, Request $request): Response
    {
        $customer = $this->repository->findById($id);

        if ($customer === null) {
            throw $this->createNotFoundException('Customer not found.');
        }

        if ($this->isCsrfTokenValid('delete_customer_' . $id, (string) $request->request->get('_token'))) {
            $this->repository->remove($customer);
            $this->addFlash('success', 'Customer deleted successfully.');
        }

        return $this->redirectToRoute('app_customer_index');
    }
}
