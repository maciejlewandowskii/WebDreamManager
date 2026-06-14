<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Entity;

use App\Domain\Notifications\Infrastructure\DoctrineNotificationRuleRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DoctrineNotificationRuleRepository::class)]
#[ORM\Table(name: 'notification_rules')]
#[ORM\Index(name: 'idx_notification_rule_event', columns: ['event_name', 'is_active'])]
class NotificationRule
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $eventName;

    /** @var string[] stored as raw NotificationChannelType values */
    #[ORM\Column(type: 'json')]
    private array $channels = [];

    /**
     * When set, only users whose role has this permission receive the notification.
     * Stored as Permission::value string (e.g. "invoice.list").
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $requiredPermission = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    /** @param NotificationChannelType[] $channels */
    public function __construct(string $eventName, array $channels)
    {
        $this->eventName = $eventName;
        $this->channels  = array_values(array_map(static fn(NotificationChannelType $c) => $c->value, $channels));
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): string { return (string) $this->id; }

    public function getEventName(): string { return $this->eventName; }

    /** @return NotificationChannelType[] */
    public function getChannels(): array
    {
        return array_map(static fn(string $v) => NotificationChannelType::from($v), $this->channels);
    }

    /** @param NotificationChannelType[] $channels */
    public function setChannels(array $channels): void
    {
        $this->channels = array_values(array_map(static fn(NotificationChannelType $c) => $c->value, $channels));
    }

    public function hasChannel(NotificationChannelType $type): bool
    {
        return in_array($type->value, $this->channels, true);
    }

    public function getRequiredPermission(): ?string { return $this->requiredPermission; }

    public function setRequiredPermission(?string $permission): void
    {
        $this->requiredPermission = $permission;
    }

    public function isActive(): bool { return $this->isActive; }

    public function setActive(bool $isActive): void { $this->isActive = $isActive; }

    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
}
