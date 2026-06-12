<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\TwoFactor\Sms;

interface SmsTwoFactorInterface
{
    public function isSmsAuthEnabled(): bool;

    public function getSmsAuthRecipient(): ?string;

    public function getSmsAuthCode(): ?string;

    public function setSmsAuthCode(string $code): void;
}
