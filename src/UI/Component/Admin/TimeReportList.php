<?php

declare(strict_types=1);

namespace App\UI\Component\Admin;

use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Domain\TimeTracking\Repository\TimeRecordRepositoryInterface;
use DateTimeImmutable;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TimeReportList
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $search = '';

    #[LiveProp(writable: true, url: true)]
    public string $sortBy = 'fullName';

    #[LiveProp(writable: true, url: true)]
    public string $sortDirection = 'ASC';

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TimeRecordRepositoryInterface $recordRepository,
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function getSummaries(): array
    {
        $now = new DateTimeImmutable();

        $users = $this->userRepository->findFiltered(
            search: $this->search !== '' ? $this->search : null,
            sortBy: 'fullName',
            sortDirection: $this->sortBy === 'fullName' ? $this->sortDirection : 'ASC',
        );

        $summaries = [];
        foreach ($users as $user) {
            $monthlyArray = $this->recordRepository->sumHoursByMonthForUser(
                (int) $now->format('Y'),
                (int) $now->format('n'),
                $user,
            );
            $todayTotal = $this->recordRepository->sumHoursByDateForUser(
                new DateTimeImmutable('today'),
                $user,
            );

            // Calculate totals
            $monthlyTotal = array_sum($monthlyArray);

            $summaries[] = [
                'user'    => $user,
                'monthly' => $monthlyArray,
                'monthlyTotal' => $monthlyTotal,
                'today'   => $todayTotal,
                'todayTotal' => array_sum($todayTotal), // usually 1 day
            ];
        }

        // Apply sorting on the summaries array if sorting by today or month
        if ($this->sortBy !== 'fullName') {
            usort($summaries, function ($a, $b) {
                $valA = $this->sortBy === 'today' ? $a['todayTotal'] : $a['monthlyTotal'];
                $valB = $this->sortBy === 'today' ? $b['todayTotal'] : $b['monthlyTotal'];

                if ($valA === $valB) {
                    return 0;
                }

                $result = $valA < $valB ? -1 : 1;
                return $this->sortDirection === 'ASC' ? $result : -$result;
            });
        }

        return $summaries;
    }

    public function getMonth(): DateTimeImmutable
    {
        return new DateTimeImmutable();
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
}
