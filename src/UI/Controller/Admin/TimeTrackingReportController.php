<?php

declare(strict_types=1);

namespace App\UI\Controller\Admin;

use App\Domain\Authorization\Entity\Permission;
use App\UI\Controller\AppController;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Permission::TimeRecordViewSummary->value)]
#[Route('/admin/time-report', name: 'app_admin_time_report_')]
final class TimeTrackingReportController extends AppController
{
    #[Route('', name: 'index')]
    public function index(): Response
    {
        $now = new DateTimeImmutable();
        return $this->render('views/admin/time/summary.html.twig', [
            'month'     => $now,
        ]);
    }
}
