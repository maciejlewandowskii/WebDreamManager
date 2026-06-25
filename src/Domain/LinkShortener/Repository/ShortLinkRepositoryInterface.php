<?php

declare(strict_types=1);

namespace App\Domain\LinkShortener\Repository;

use App\Domain\LinkShortener\Entity\ShortLink;

interface ShortLinkRepositoryInterface
{
    public function findById(string $id): ?ShortLink;

    public function findByCode(string $code): ?ShortLink;

    public function codeExists(string $code): bool;

    /** @return ShortLink[] */
    public function findFiltered(
        ?string $search,
        string $sortBy = 'createdAt',
        string $sortDirection = 'DESC',
        int $offset = 0,
        int $limit = 0,
    ): array;

    public function countFiltered(?string $search): int;

    public function save(ShortLink $shortLink, bool $flush = true): void;

    public function remove(ShortLink $shortLink, bool $flush = true): void;
}
