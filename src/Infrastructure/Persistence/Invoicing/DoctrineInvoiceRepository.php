<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Invoicing;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\InvoiceStatus;
use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
final class DoctrineInvoiceRepository extends ServiceEntityRepository implements InvoiceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function findById(string $id): ?Invoice
    {
        return $this->find($id);
    }

    public function findByNumber(string $number): ?Invoice
    {
        return $this->findOneBy(['number' => $number]);
    }

    public function findByCustomer(Customer $customer): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('i.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(InvoiceStatus $status): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.status = :status')
            ->setParameter('status', $status->value)
            ->orderBy('i.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.customer', 'c')
            ->addSelect('c')
            ->orderBy('i.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getNextNumber(): string
    {
        $year = date('Y');
        $count = (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.number LIKE :prefix')
            ->setParameter('prefix', 'INV/' . $year . '/%')
            ->getQuery()
            ->getSingleScalarResult();

        return sprintf('INV/%s/%04d', $year, $count + 1);
    }

    public function save(Invoice $invoice, bool $flush = true): void
    {
        $this->getEntityManager()->persist($invoice);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Invoice $invoice, bool $flush = true): void
    {
        $this->getEntityManager()->remove($invoice);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
