<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Repository;

use App\Domain\Identity\Entity\User;
use App\Domain\Project\Entity\Project;
use App\Domain\TimeTracking\Entity\TimeRecord;
use DateTimeImmutable;

interface TimeRecordRepositoryInterface
{
    public function findById(string $id): ?TimeRecord;

    /** @return TimeRecord[] */
    public function findAll(): array;

    /** @return TimeRecord[] */
    public function findByProject(Project $project): array;

    /** @return TimeRecord[] */
    public function findByWorker(User $worker): array;

    /** @return TimeRecord[] */
    public function findByDateRange(DateTimeImmutable $from, DateTimeImmutable $to): array;

    /** @return TimeRecord[] */
    public function findUninvoicedByProject(Project $project): array;

    /**
     * @param string[] $visibleProjectIds Project IDs where records from any worker are visible
     * @return TimeRecord[]
     */
    public function findFiltered(
        ?string $search,
        ?Project $project,
        bool $uninvoicedOnly = false,
        string $sortBy = 'date',
        string $sortDirection = 'DESC',
        ?User $workerFilter = null,
        array $visibleProjectIds = [],
        ?\DateTimeImmutable $dateFilter = null,
        int $offset = 0,
        int $limit = 0,
    ): array;

    /**
     * @param string[] $visibleProjectIds
     */
    public function countFiltered(
        ?string $search,
        ?Project $project,
        bool $uninvoicedOnly = false,
        ?User $workerFilter = null,
        array $visibleProjectIds = [],
        ?\DateTimeImmutable $dateFilter = null,
    ): int;

    /** @return array{spent: float, estimated: float} */
    public function sumHoursByDate(DateTimeImmutable $date): array;

    /** @return array{spent: float} */
    public function sumHoursByMonth(int $year, int $month): array;

    /** @return array{spent: float, estimated: float} */
    public function sumHoursByDateForUser(DateTimeImmutable $date, User $user): array;

    /** @return array{spent: float} */
    public function sumHoursByMonthForUser(int $year, int $month, User $user): array;

    public function save(TimeRecord $record, bool $flush = true): void;

    public function remove(TimeRecord $record, bool $flush = true): void;
}
