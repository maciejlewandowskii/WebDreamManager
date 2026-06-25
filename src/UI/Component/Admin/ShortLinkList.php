<?php

declare(strict_types=1);

namespace App\UI\Component\Admin;

use App\Domain\LinkShortener\Entity\ShortLink;
use App\Domain\LinkShortener\Repository\ShortLinkRepositoryInterface;
use App\UI\Component\LivePaginationTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ShortLinkList
{
    use DefaultActionTrait;
    use LivePaginationTrait;

    private const int PER_PAGE = 50;

    #[LiveProp(writable: true)]
    public string $search = '';

    #[LiveProp(writable: true)]
    public string $sortBy = 'createdAt';

    #[LiveProp(writable: true)]
    public string $sortDirection = 'DESC';

    public function __construct(private readonly ShortLinkRepositoryInterface $repository)
    {
    }

    /** @return ShortLink[] */
    public function getLinks(): array
    {
        return $this->repository->findFiltered(
            $this->search !== '' ? $this->search : null,
            $this->sortBy,
            $this->sortDirection,
            self::PER_PAGE * ($this->page - 1),
            self::PER_PAGE,
        );
    }

    public function getTotal(): int
    {
        return $this->repository->countFiltered($this->search !== '' ? $this->search : null);
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
        $this->search = '';
        $this->page   = 1;
    }
}
