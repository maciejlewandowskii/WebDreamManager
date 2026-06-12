<?php

declare(strict_types=1);

namespace App\Domain\Identity\Entity;

use App\Domain\Authorization\Entity\Role;
use App\Domain\Identity\Infrastructure\DoctrineUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as EmailTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: DoctrineUserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EmailTwoFactorInterface, TotpTwoFactorInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private string $fullName;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $avatarUrl = null;

    #[ORM\Column(type: 'smallint')]
    private int $workingHoursPerDay = 8;

    // 2FA — Email
    #[ORM\Column(type: 'boolean')]
    private bool $emailAuthEnabled = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $emailAuthCode = null;

    // 2FA — TOTP
    #[ORM\Column(type: 'boolean')]
    private bool $totpAuthEnabled = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $totpSecret = null;

    // 2FA — Backup codes
    #[ORM\Column(type: 'json')]
    private array $backupCodes = [];

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Role $role = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true, unique: true)]
    private ?string $setupToken = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $setupTokenExpiresAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(string $email, string $fullName)
    {
        $this->email = $email;
        $this->fullName = $fullName;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): void
    {
        $this->avatarUrl = $avatarUrl;
    }

    public function getWorkingHoursPerDay(): int
    {
        return $this->workingHoursPerDay;
    }

    public function setWorkingHoursPerDay(int $hours): void
    {
        $this->workingHoursPerDay = $hours;
    }

    public function eraseCredentials(): void
    {
    }

    // --- Email 2FA ---

    public function isEmailAuthEnabled(): bool
    {
        return $this->emailAuthEnabled;
    }

    public function setEmailAuthEnabled(bool $enabled): void
    {
        $this->emailAuthEnabled = $enabled;
    }

    public function getEmailAuthRecipient(): string
    {
        return $this->email;
    }

    public function getEmailAuthCode(): string
    {
        return (string) $this->emailAuthCode;
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->emailAuthCode = $authCode;
    }

    // --- TOTP 2FA ---

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->totpAuthEnabled && $this->totpSecret !== null;
    }

    public function setTotpAuthEnabled(bool $enabled): void
    {
        $this->totpAuthEnabled = $enabled;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->email;
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        if ($this->totpSecret === null) {
            return null;
        }

        return new TotpConfiguration($this->totpSecret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $secret): void
    {
        $this->totpSecret = $secret;
    }

    // --- Backup codes ---

    public function isBackupCode(string $code): bool
    {
        return in_array($code, $this->backupCodes, true);
    }

    public function invalidateBackupCode(string $code): void
    {
        $this->backupCodes = array_values(array_filter(
            $this->backupCodes,
            static fn (string $c) => $c !== $code,
        ));
    }

    public function setBackupCodes(array $codes): void
    {
        $this->backupCodes = $codes;
    }

    public function getBackupCodes(): array
    {
        return $this->backupCodes;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): void
    {
        $this->role = $role;
    }

    public function getSetupToken(): ?string
    {
        return $this->setupToken;
    }

    public function generateSetupToken(): string
    {
        $this->setupToken = bin2hex(random_bytes(32));
        $this->setupTokenExpiresAt = new DateTimeImmutable('+7 days');

        return $this->setupToken;
    }

    public function clearSetupToken(): void
    {
        $this->setupToken = null;
        $this->setupTokenExpiresAt = null;
    }

    public function isSetupTokenValid(): bool
    {
        return $this->setupToken !== null
            && $this->setupTokenExpiresAt !== null
            && $this->setupTokenExpiresAt > new DateTimeImmutable();
    }

    public function isPendingSetup(): bool
    {
        return $this->setupToken !== null;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
