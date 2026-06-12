<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Repository;

use App\Domain\Notifications\Entity\NotificationRule;

interface NotificationRuleRepositoryInterface
{
    /** @return NotificationRule[] */
    public function findActiveByEvent(string $eventName): array;

    /** @return NotificationRule[] */
    public function findAll(): array;

    public function find(string $id): ?NotificationRule;

    public function save(NotificationRule $rule): void;

    public function remove(NotificationRule $rule): void;
}
