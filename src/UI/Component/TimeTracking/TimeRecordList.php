<?php

declare(strict_types=1);

namespace App\UI\Component\TimeTracking;

use App\Domain\Authorization\Entity\Permission;
use App\Domain\Authorization\Repository\ProjectMemberRepositoryInterface;
use App\Domain\Identity\Entity\User;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\TimeTracking\Entity\TimeRecord;
use App\Domain\TimeTracking\Repository\TimeRecordRepositoryInterface;
use App\UI\Component\LivePaginationTrait;
use DateTimeImmutable;
use Silvesterk\BusinessDays\BusinessDays;
use Silvesterk\BusinessDays\DateRange;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TimeRecordList
{
    use DefaultActionTrait;
    use LivePaginationTrait;

    private const int PER_PAGE = 50;

    #[LiveProp(writable: true)]
    public string $search = '';

    #[LiveProp(writable: true)]
    public string $projectId = '';

    #[LiveProp(writable: true)]
    public bool $uninvoicedOnly = false;

    #[LiveProp(writable: true)]
    public string $sortBy = 'date';

    #[LiveProp(writable: true)]
    public string $sortDirection = 'DESC';

    #[LiveProp]
    public ?string $monthlyBudget = null;

    #[LiveProp(writable: true, url: true)]
    public string $viewMode = 'all';

    #[LiveProp(writable: true, url: true)]
    public string $currentDate = '';

    public function __construct(
        private readonly TimeRecordRepositoryInterface $recordRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly ProjectMemberRepositoryInterface $memberRepository,
        private readonly Security $security,
    ) {
    }

    public function canViewAll(): bool
    {
        return $this->security->isGranted(Permission::TimeRecordViewAll->value);
    }

    public function canManageAll(): bool
    {
        return $this->security->isGranted(Permission::TimeRecordManageAll->value);
    }

    /** @return TimeRecord[] */
    public function getRecords(): array
    {
        [$project, $workerFilter, $visibleProjectIds, $dateFilter] = $this->resolveFilterArgs();

        return $this->recordRepository->findFiltered(
            search: $this->search !== '' ? $this->search : null,
            project: $project,
            uninvoicedOnly: $this->uninvoicedOnly,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
            workerFilter: $workerFilter,
            visibleProjectIds: $visibleProjectIds,
            dateFilter: $dateFilter,
            offset: ($this->page - 1) * self::PER_PAGE,
            limit: self::PER_PAGE,
        );
    }

    public function getTotal(): int
    {
        [$project, $workerFilter, $visibleProjectIds, $dateFilter] = $this->resolveFilterArgs();

        return $this->recordRepository->countFiltered(
            search: $this->search !== '' ? $this->search : null,
            project: $project,
            uninvoicedOnly: $this->uninvoicedOnly,
            workerFilter: $workerFilter,
            visibleProjectIds: $visibleProjectIds,
            dateFilter: $dateFilter,
        );
    }

    /** @return array{0: ?\App\Domain\Project\Entity\Project, 1: ?User, 2: string[], 3: ?\DateTimeImmutable} */
    private function resolveFilterArgs(): array
    {
        $project = $this->projectId !== ''
            ? $this->projectRepository->findById($this->projectId)
            : null;

        [$workerFilter, $visibleProjectIds] = $this->resolveVisibility();

        $dateFilter = null;
        if ($this->viewMode === 'per_day') {
            $dateFilter = $this->currentDate !== ''
                ? new \DateTimeImmutable($this->currentDate)
                : new \DateTimeImmutable('today');
        }

        return [$project, $workerFilter, $visibleProjectIds, $dateFilter];
    }

    #[LiveAction]
    public function sortBy(#[LiveArg] string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';

            return;
        }

        $this->sortBy = $field;
        $this->sortDirection = 'ASC';
    }

    #[LiveAction]
    public function setViewMode(#[LiveArg] string $mode): void
    {
        $this->viewMode = $mode;
    }

    #[LiveAction]
    public function prevDay(): void
    {
        $date = $this->currentDate !== '' ? new \DateTimeImmutable($this->currentDate) : new \DateTimeImmutable('today');
        $this->currentDate = $date->modify('-1 day')->format('Y-m-d');
    }

    #[LiveAction]
    public function nextDay(): void
    {
        $date = $this->currentDate !== '' ? new \DateTimeImmutable($this->currentDate) : new \DateTimeImmutable('today');
        $this->currentDate = $date->modify('+1 day')->format('Y-m-d');
    }

    #[LiveAction]
    public function today(): void
    {
        $this->currentDate = (new \DateTimeImmutable('today'))->format('Y-m-d');
    }

    /** @return array<string, mixed> */
    public function getTodayStats(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if ($this->canViewAll()) {
            return $this->recordRepository->sumHoursByDate(new DateTimeImmutable('today'));
        }

        return $this->recordRepository->sumHoursByDateForUser(new DateTimeImmutable('today'), $user);
    }

    /** @return array{spent: mixed, expected: int, days: int} */
    public function getMonthlyStats(): array
    {
        $now = new DateTimeImmutable();
        /** @var User $user */
        $user = $this->security->getUser();

        $spent = $this->canViewAll()
            ? $this->recordRepository->sumHoursByMonth((int) $now->format('Y'), (int) $now->format('n'))
            : $this->recordRepository->sumHoursByMonthForUser((int) $now->format('Y'), (int) $now->format('n'), $user);

        $hoursPerDay = $user->getWorkingHoursPerDay();

        $bd = new BusinessDays();
        $workingDays = $bd->getBusinessDayNumberFromRange(
            new DateRange($now->format('Y-m-01'), $now->format('Y-m-t')),
        );
        $expected = $workingDays * $hoursPerDay;

        return [
            'spent'    => $spent['spent'],
            'expected' => $expected,
            'days'     => $workingDays,
        ];
    }

    /** @return array{0: ?User, 1: string[]} */
    private function resolveVisibility(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if ($this->canViewAll()) {
            return [null, []];
        }

        $visibleProjectIds = $this->memberRepository->findProjectIdsWithPermission(
            $user,
            Permission::TimeRecordViewAll,
        );

        return [$user, $visibleProjectIds];
    }
}
