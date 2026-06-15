<?php

declare(strict_types=1);

namespace App\UI\Component;

use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

trait LivePaginationTrait
{
    #[LiveProp(writable: true)]
    public int $page = 1;

    abstract public function getTotal(): int;

    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotal() / static::PER_PAGE);
    }

    #[LiveAction]
    public function goToPage(#[LiveArg] int $page): void
    {
        $this->page = max(1, $page);
    }
}
