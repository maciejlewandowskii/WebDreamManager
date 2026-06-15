<?php

declare(strict_types=1);

namespace App\Domain\Logging\Repository;

use App\Domain\Logging\Application\Data\LogFilter;
use App\Domain\Logging\Entity\LogEntry;
use DateTimeImmutable;

interface LogRepositoryInterface
{
    public function findById(string $id): ?LogEntry;

    /** @return LogEntry[] */
    public function filter(LogFilter $filter): array;

    public function countByFilter(LogFilter $filter): int;

    /** @return string[] */
    public function findDistinctCategories(): array;

    /** @return string[] */
    public function findDistinctServices(): array;

    public function save(LogEntry $entry, bool $flush = true): void;

    public function deleteOlderThan(DateTimeImmutable $before): int;
}
