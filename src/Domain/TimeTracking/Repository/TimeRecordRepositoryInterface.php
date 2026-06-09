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

    /** @return TimeRecord[] */
    public function findFiltered(?string $search, ?Project $project, bool $uninvoicedOnly = false): array;

    public function save(TimeRecord $record, bool $flush = true): void;

    public function remove(TimeRecord $record, bool $flush = true): void;
}
