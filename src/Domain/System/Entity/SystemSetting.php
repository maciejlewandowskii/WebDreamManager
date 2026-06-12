<?php

declare(strict_types=1);

namespace App\Domain\System\Entity;

use App\Domain\System\Infrastructure\DoctrineSystemSettingRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineSystemSettingRepository::class)]
#[ORM\Table(name: 'system_settings')]
class SystemSetting
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 100)]
    private string $key;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $value;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(string $key, ?string $value)
    {
        $this->key       = $key;
        $this->value     = $value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getKey(): string { return $this->key; }

    public function getValue(): ?string { return $this->value; }

    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function setValue(?string $value): void
    {
        $this->value     = $value;
        $this->updatedAt = new DateTimeImmutable();
    }
}
