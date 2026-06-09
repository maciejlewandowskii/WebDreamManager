<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Sms;

use App\Domain\Communications\Port\SmsSenderInterface;

final class StubSmsSender implements SmsSenderInterface
{
    public function send(string $to, string $message): bool
    {
        return true;
    }

    public function isConfigured(): bool
    {
        return false;
    }
}
