<?php

declare(strict_types=1);

namespace App\Domain\LinkShortener\Entity;

use App\Domain\LinkShortener\Infrastructure\DoctrineShortLinkRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DoctrineShortLinkRepository::class)]
#[ORM\Table(name: 'short_links')]
class ShortLink
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 20, unique: true)]
    private string $code;

    #[ORM\Column(type: 'text')]
    private string $targetUrl;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $sourceType = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $sourceLabel = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $clickCount = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastClickedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(string $code, string $targetUrl, ?string $sourceType = null, ?string $sourceLabel = null)
    {
        $this->code        = $code;
        $this->targetUrl   = $targetUrl;
        $this->sourceType  = $sourceType;
        $this->sourceLabel = $sourceLabel;
        $this->createdAt   = new DateTimeImmutable();
    }

    public function recordClick(): void
    {
        $this->clickCount++;
        $this->lastClickedAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return (string) $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }

    public function getSourceType(): ?string
    {
        return $this->sourceType;
    }

    public function getSourceLabel(): ?string
    {
        return $this->sourceLabel;
    }

    public function getClickCount(): int
    {
        return $this->clickCount;
    }

    public function getLastClickedAt(): ?DateTimeImmutable
    {
        return $this->lastClickedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
