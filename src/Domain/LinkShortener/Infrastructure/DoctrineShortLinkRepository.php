<?php

declare(strict_types=1);

namespace App\Domain\LinkShortener\Infrastructure;

use App\Domain\LinkShortener\Entity\ShortLink;
use App\Domain\LinkShortener\Repository\ShortLinkRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShortLink>
 */
final class DoctrineShortLinkRepository extends ServiceEntityRepository implements ShortLinkRepositoryInterface
{
    private const SORT_FIELDS = [
        'code'           => 's.code',
        'targetUrl'      => 's.targetUrl',
        'sourceLabel'    => 's.sourceLabel',
        'clickCount'     => 's.clickCount',
        'lastClickedAt'  => 's.lastClickedAt',
        'createdAt'      => 's.createdAt',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShortLink::class);
    }

    public function findById(string $id): ?ShortLink
    {
        return $this->find($id);
    }

    public function findByCode(string $code): ?ShortLink
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function codeExists(string $code): bool
    {
        return $this->findOneBy(['code' => $code]) !== null;
    }

    public function findFiltered(
        ?string $search,
        string $sortBy = 'createdAt',
        string $sortDirection = 'DESC',
        int $offset = 0,
        int $limit = 0,
    ): array {
        [$field, $direction] = $this->resolveSorting($sortBy, $sortDirection);

        $qb = $this->createQueryBuilder('s')->orderBy($field, $direction);

        if ($search !== null && $search !== '') {
            $qb->andWhere('s.code LIKE :search OR s.targetUrl LIKE :search OR s.sourceLabel LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function countFiltered(?string $search): int
    {
        $qb = $this->createQueryBuilder('s')->select('COUNT(s.id)');

        if ($search !== null && $search !== '') {
            $qb->andWhere('s.code LIKE :search OR s.targetUrl LIKE :search OR s.sourceLabel LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function save(ShortLink $shortLink, bool $flush = true): void
    {
        $this->getEntityManager()->persist($shortLink);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ShortLink $shortLink, bool $flush = true): void
    {
        $this->getEntityManager()->remove($shortLink);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** @return array{0: string, 1: 'ASC'|'DESC'} */
    private function resolveSorting(string $sortBy, string $sortDirection): array
    {
        $field     = self::SORT_FIELDS[$sortBy] ?? self::SORT_FIELDS['createdAt'];
        $direction = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';

        return [$field, $direction];
    }
}
