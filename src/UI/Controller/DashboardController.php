<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Invoicing\Entity\InvoiceStatus;
use App\Domain\Invoicing\Entity\QuoteStatus;
use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use App\Domain\Invoicing\Repository\QuoteRepositoryInterface;
use App\Domain\Project\Entity\ProjectStatus;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\TimeTracking\Repository\TimeRecordRepositoryInterface;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly InvoiceRepositoryInterface $invoices,
        private readonly QuoteRepositoryInterface $quotes,
        private readonly TimeRecordRepositoryInterface $timeRecords,
    ) {
    }

    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        $now = new DateTimeImmutable();
        $monthStart = $now->modify('first day of this month midnight');
        $monthEnd = $now->modify('last day of this month 23:59:59');

        $monthRecords = $this->timeRecords->findByDateRange($monthStart, $monthEnd);
        $monthHours = array_sum(array_map(static fn ($r) => (float) $r->getSpentHours(), $monthRecords));

        $activeProjects = count(array_filter(
            $this->projects->findAll(),
            static fn ($p) => $p->getStatus() === ProjectStatus::Active,
        ));

        $recentTimeRecords = array_slice(
            array_reverse($this->timeRecords->findByDateRange(
                $now->modify('-30 days'),
                $now,
            )),
            0,
            5,
        );

        return $this->render('views/dashboard/index.html.twig', [
            'activeProjects'   => $activeProjects,
            'openInvoices'     => count($this->invoices->findByStatus(InvoiceStatus::Sent)),
            'pendingQuotes'    => count($this->quotes->findByStatus(QuoteStatus::Sent)),
            'monthHours'       => $monthHours,
            'recentInvoices'   => array_slice($this->invoices->findAll(), 0, 5),
            'recentTimeRecords' => $recentTimeRecords,
        ]);
    }
}
