<?php

declare(strict_types=1);

namespace App\UI\Controller\Admin;

use App\Domain\Authorization\Entity\Permission;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Domain\TimeTracking\Repository\TimeRecordRepositoryInterface;
use App\UI\Controller\AppController;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Permission::TimeRecordViewSummary->value)]
#[Route('/admin/time-report', name: 'app_admin_time_report_')]
final class TimeTrackingReportController extends AppController
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TimeRecordRepositoryInterface $recordRepository,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $now = new DateTimeImmutable();
        return $this->render('views/admin/time/summary.html.twig', [
            'month'     => $now,
        ]);
    }
}
