<?php

declare(strict_types=1);

namespace App\Domain\Identity\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'passkey_credentials')]
class PasskeyCredential
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'text')]
    private string $credentialId;

    #[ORM\Column(type: 'text')]
    private string $publicKey;

    #[ORM\Column(type: 'integer')]
    private int $signCount;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastUsedAt = null;

    public function __construct(
        User $user,
        string $credentialId,
        string $publicKey,
        int $signCount,
        string $name,
    ) {
        $this->user        = $user;
        $this->credentialId = $credentialId;
        $this->publicKey   = $publicKey;
        $this->signCount   = $signCount;
        $this->name        = $name;
        $this->createdAt   = new DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getCredentialId(): string { return $this->credentialId; }
    public function getPublicKey(): string { return $this->publicKey; }
    public function getSignCount(): int { return $this->signCount; }
    public function getName(): string { return $this->name; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getLastUsedAt(): ?DateTimeImmutable { return $this->lastUsedAt; }

    public function setSignCount(int $count): void { $this->signCount = $count; }
    public function setLastUsedAt(DateTimeImmutable $dt): void { $this->lastUsedAt = $dt; }
}
