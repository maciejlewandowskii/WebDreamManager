<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Email;

use App\Infrastructure\Communications\Port\EmailSenderInterface;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

final readonly class TwoFactorEmailMailer implements AuthCodeMailerInterface
{
    public function __construct(private EmailSenderInterface $mailer) {}

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $code = $user->getEmailAuthCode();
        if ($code === null) {
            return;
        }

        $this->mailer->sendTemplate(
            [$user->getEmailAuthRecipient()],
            'Your WebDream Manager authentication code',
            'views/identity/two_factor/email.html.twig',
            ['code' => $code],
        );
    }
}
