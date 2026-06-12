<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\TwoFactor\Sms;

use App\Infrastructure\Communications\Port\SmsSenderInterface;

final readonly class SmsCodeSender
{
    public function __construct(private SmsSenderInterface $smsSender) {}

    public function sendCode(SmsTwoFactorInterface $user): void
    {
        $phone = $user->getSmsAuthRecipient();
        if ($phone === null) {
            return;
        }

        $this->smsSender->send($phone, 'Your WebDream Manager login code: ' . $user->getSmsAuthCode());
    }
}
