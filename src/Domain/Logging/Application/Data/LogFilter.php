<?php

declare(strict_types=1);

namespace App\Domain\Logging\Application\Data;

use App\Domain\Logging\Entity\LogLevel;
use App\Domain\Logging\Entity\LogType;
use DateTimeImmutable;

final class LogFilter
{
    public ?LogType $type = null;
    public ?LogLevel $level = null;
    public ?string $category = null;
    public ?string $service = null;
    public ?string $userId = null;
    public ?string $search = null;
    public ?DateTimeImmutable $dateFrom = null;
    public ?DateTimeImmutable $dateTo = null;
    public int $page = 1;
    public int $perPage = 50;
    public string $sortBy = 'createdAt';
    public string $sortDirection = 'DESC';
}
