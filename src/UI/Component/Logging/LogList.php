<?php

declare(strict_types=1);

namespace App\UI\Component\Logging;

use App\Domain\Logging\Application\Data\LogFilter;
use App\Domain\Logging\Entity\LogEntry;
use App\Domain\Logging\Entity\LogLevel;
use App\Domain\Logging\Entity\LogType;
use App\Domain\Logging\Repository\LogRepositoryInterface;
use App\UI\Component\LivePaginationTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class LogList
{
    use DefaultActionTrait;
    use LivePaginationTrait;

    private const int PER_PAGE = 50;

    #[LiveProp(writable: true)]
    public string $search = '';

    #[LiveProp(writable: true)]
    public string $type = '';

    #[LiveProp(writable: true)]
    public string $level = '';

    #[LiveProp(writable: true)]
    public string $category = '';

    #[LiveProp(writable: true)]
    public string $service = '';

    #[LiveProp(writable: true)]
    public string $dateFrom = '';

    #[LiveProp(writable: true)]
    public string $dateTo = '';

    #[LiveProp(writable: true)]
    public string $sortBy = 'createdAt';

    #[LiveProp(writable: true)]
    public string $sortDirection = 'DESC';

    public function __construct(
        private readonly LogRepositoryInterface $repository,
    ) {
    }

    /** @return LogEntry[] */
    public function getEntries(): array
    {
        return $this->repository->filter($this->buildFilter());
    }

    public function getTotal(): int
    {
        return $this->repository->countByFilter($this->buildFilter());
    }

    /** @return string[] */
    public function getAvailableCategories(): array
    {
        return $this->repository->findDistinctCategories();
    }

    /** @return string[] */
    public function getAvailableServices(): array
    {
        return $this->repository->findDistinctServices();
    }

    /** @return LogType[] */
    public function getLogTypes(): array
    {
        return LogType::cases();
    }

    /** @return LogLevel[] */
    public function getLogLevels(): array
    {
        return LogLevel::cases();
    }

    #[LiveAction]
    public function sortBy(#[LiveArg] string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';

            return;
        }

        $this->sortBy        = $field;
        $this->sortDirection = 'DESC';
    }

    #[LiveAction]
    public function resetFilters(): void
    {
        $this->search    = '';
        $this->type      = '';
        $this->level     = '';
        $this->category  = '';
        $this->service   = '';
        $this->dateFrom  = '';
        $this->dateTo    = '';
        $this->page      = 1;
    }

    private function parseDate(string $value, string $time = '00:00:00'): ?\DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable("$value $time");
        } catch (\DateMalformedStringException) {
            return null;
        }
    }

    private function buildFilter(): LogFilter
    {
        $filter               = new LogFilter();
        $filter->search       = $this->search !== '' ? $this->search : null;
        $filter->type         = $this->type !== '' ? LogType::from($this->type) : null;
        $filter->level        = $this->level !== '' ? LogLevel::from($this->level) : null;
        $filter->category     = $this->category !== '' ? $this->category : null;
        $filter->service      = $this->service !== '' ? $this->service : null;
        $filter->dateFrom     = $this->parseDate($this->dateFrom);
        $filter->dateTo       = $this->parseDate($this->dateTo, '23:59:59');
        $filter->page         = $this->page;
        $filter->perPage      = self::PER_PAGE;
        $filter->sortBy       = $this->sortBy;
        $filter->sortDirection = $this->sortDirection;

        return $filter;
    }
}
