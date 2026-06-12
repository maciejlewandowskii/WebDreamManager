<?php

declare(strict_types=1);

namespace App\Infrastructure\Communications\Port;

interface EmailSenderInterface
{
    /**
     * @param string[] $to
     * @param array<string, mixed> $context
     */
    public function sendTemplate(array $to, string $subject, string $template, array $context = []): void;

    public function sendRaw(string $to, string $subject, string $htmlBody, string $textBody = ''): void;
}
