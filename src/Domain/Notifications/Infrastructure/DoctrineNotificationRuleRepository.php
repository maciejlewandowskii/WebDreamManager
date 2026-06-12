<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Infrastructure;

use App\Domain\Notifications\Entity\NotificationRule;
use App\Domain\Notifications\Repository\NotificationRuleRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationRule>
 */
final class DoctrineNotificationRuleRepository extends ServiceEntityRepository implements NotificationRuleRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationRule::class);
    }

    public function findActiveByEvent(string $eventName): array
    {
        return $this->findBy(['eventName' => $eventName, 'isActive' => true]);
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?NotificationRule
    {
        $result = parent::find($id, $lockMode, $lockVersion);

        return $result instanceof NotificationRule ? $result : null;
    }

    public function save(NotificationRule $rule): void
    {
        $this->getEntityManager()->persist($rule);
        $this->getEntityManager()->flush();
    }

    public function remove(NotificationRule $rule): void
    {
        $this->getEntityManager()->remove($rule);
        $this->getEntityManager()->flush();
    }
}
