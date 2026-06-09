<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Invoicing\Entity\InvoiceStatus;
use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly ProjectRepositoryInterface $projects,
        private readonly InvoiceRepositoryInterface $invoices,
    ) {
    }

    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'stats' => [
                'customers'       => count($this->customers->findAll()),
                'active_projects' => count($this->projects->findAll()),
                'open_invoices'   => count($this->invoices->findByStatus(InvoiceStatus::Sent)),
                'overdue_invoices'=> count($this->invoices->findByStatus(InvoiceStatus::Overdue)),
            ],
            'recent_invoices' => array_slice($this->invoices->findAll(), 0, 5),
        ]);
    }
}
