<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Sms;

use App\Domain\Logging\Application\LoggerService;
use App\Domain\Logging\Entity\LogLevel;
use App\Infrastructure\Communications\Port\SmsSenderInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;

final readonly class TwilioSmsSender implements SmsSenderInterface
{
    public function __construct(
        private TexterInterface $texter,
        private string $fromNumber,
        private LoggerService $logger,
    ) {}

    public function send(string $to, string $message): bool
    {
        $sms = new SmsMessage($to, $message, $this->fromNumber);
        $this->texter->send($sms);

        $this->logger->externalService(
            LogLevel::Info,
            'SMS sent to ' . $to,
            'twilio',
            ['to' => $to, 'length' => strlen($message)],
        );

        return true;
    }

    public function isConfigured(): bool
    {
        return true;
    }
}
