<?php

declare(strict_types=1);

namespace App\Domain\Communications\Port;

interface SmsSenderInterface
{
    public function send(string $to, string $message): bool;

    public function isConfigured(): bool;
}
