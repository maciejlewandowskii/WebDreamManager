<?php

declare(strict_types=1);

namespace App\Domain\Logging\Entity;

use App\Domain\Logging\Infrastructure\DoctrineLogRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DoctrineLogRepository::class)]
#[ORM\Table(name: 'log_entries')]
#[ORM\Index(name: 'idx_log_type', columns: ['type'])]
#[ORM\Index(name: 'idx_log_level', columns: ['level'])]
#[ORM\Index(name: 'idx_log_category', columns: ['category'])]
#[ORM\Index(name: 'idx_log_service', columns: ['service'])]
#[ORM\Index(name: 'idx_log_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_log_created_at', columns: ['created_at'])]
class LogEntry
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 50, enumType: LogType::class)]
    private LogType $type;

    #[ORM\Column(type: 'string', length: 20, enumType: LogLevel::class)]
    private LogLevel $level;

    #[ORM\Column(type: 'string', length: 100)]
    private string $category;

    #[ORM\Column(type: 'text')]
    private string $message;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $context;

    #[ORM\Column(name: 'user_id', type: 'string', length: 50, nullable: true)]
    private ?string $userId;

    #[ORM\Column(name: 'user_name', type: 'string', length: 200, nullable: true)]
    private ?string $userName;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $service;

    #[ORM\Column(name: 'ip_address', type: 'string', length: 50, nullable: true)]
    private ?string $ipAddress;

    #[ORM\Column(name: 'request_id', type: 'string', length: 100, nullable: true)]
    private ?string $requestId;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    /** @param array<string, mixed>|null $context */
    public function __construct(
        LogType $type,
        LogLevel $level,
        string $category,
        string $message,
        ?array $context = null,
        ?string $userId = null,
        ?string $userName = null,
        ?string $service = null,
        ?string $ipAddress = null,
        ?string $requestId = null,
    ) {
        $this->type      = $type;
        $this->level     = $level;
        $this->category  = $category;
        $this->message   = $message;
        $this->context   = $context;
        $this->userId    = $userId;
        $this->userName  = $userName;
        $this->service   = $service;
        $this->ipAddress = $ipAddress;
        $this->requestId = $requestId;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getType(): LogType
    {
        return $this->type;
    }

    public function getLevel(): LogLevel
    {
        return $this->level;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /** @return array<string, mixed>|null */
    public function getContext(): ?array
    {
        return $this->context;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
