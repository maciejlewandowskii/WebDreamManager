<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Email;

use App\Domain\Communications\Port\EmailSenderInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class SymfonyMailerEmailSender implements EmailSenderInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromAddress,
        private readonly string $fromName,
    ) {
    }

    public function sendTemplate(array $to, string $subject, string $template, array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->from(sprintf('%s <%s>', $this->fromName, $this->fromAddress))
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        foreach ($to as $address) {
            $email->addTo($address);
        }

        $this->mailer->send($email);
    }

    public function sendRaw(string $to, string $subject, string $htmlBody, string $textBody = ''): void
    {
        $email = (new Email())
            ->from(sprintf('%s <%s>', $this->fromName, $this->fromAddress))
            ->to($to)
            ->subject($subject)
            ->html($htmlBody);

        if ($textBody !== '') {
            $email->text($textBody);
        }

        $this->mailer->send($email);
    }
}
