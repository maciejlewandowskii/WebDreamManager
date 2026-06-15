<?php

declare(strict_types=1);

namespace App\UI\Component\Invoicing;

use App\Domain\Invoicing\Entity\Quote;
use App\Domain\Invoicing\Repository\QuoteRepositoryInterface;
use App\UI\Component\LivePaginationTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class QuoteList
{
    use DefaultActionTrait;
    use LivePaginationTrait;

    private const int PER_PAGE = 25;

    #[LiveProp(writable: true, url: true)]
    public string $search = '';

    #[LiveProp(writable: true)]
    public string $sortBy = 'createdAt';

    #[LiveProp(writable: true)]
    public string $sortDirection = 'DESC';

    public function __construct(
        private readonly QuoteRepositoryInterface $quoteRepository,
    ) {
    }

    /** @return Quote[] */
    public function getQuotes(): array
    {
        return $this->quoteRepository->findFiltered(
            search: $this->search !== '' ? $this->search : null,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
            offset: ($this->page - 1) * self::PER_PAGE,
            limit: self::PER_PAGE,
        );
    }

    public function getTotal(): int
    {
        return $this->quoteRepository->countFiltered(
            search: $this->search !== '' ? $this->search : null,
        );
    }

    #[LiveAction]
    public function sortBy(#[LiveArg] string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'ASC';
        }

        $this->page = 1;
    }
}
