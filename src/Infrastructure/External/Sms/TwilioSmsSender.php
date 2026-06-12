<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Sms;

use App\Infrastructure\Communications\Port\SmsSenderInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;

final readonly class TwilioSmsSender implements SmsSenderInterface
{
    public function __construct(
        private TexterInterface $texter,
        private string $fromNumber,
    ) {}

    public function send(string $to, string $message): bool
    {
        $sms = new SmsMessage($to, $message, $this->fromNumber);
        $this->texter->send($sms);

        return true;
    }

    public function isConfigured(): bool
    {
        return true;
    }
}
