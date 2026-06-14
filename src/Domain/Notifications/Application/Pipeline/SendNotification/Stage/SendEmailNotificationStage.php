<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Application\Pipeline\SendNotification\Stage;

use App\Domain\Integration\Application\IntegrationStatusService;
use App\Domain\Notifications\Application\Pipeline\SendNotification\SendNotificationCommand;
use App\Domain\Notifications\Entity\NotificationChannelType;
use App\Domain\Notifications\Port\NotificationChannelInterface;
use App\Infrastructure\Communications\Port\EmailSenderInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.notifications.send', attributes: ['priority' => 100])]
final readonly class SendEmailNotificationStage implements PipelineHandlerInterface, NotificationChannelInterface
{
    public function __construct(
        private EmailSenderInterface $mailer,
        private IntegrationStatusService $integrations,
    ) {
    }

    public function supports(NotificationChannelType $type): bool
    {
        return $type === NotificationChannelType::Email;
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SendNotificationCommand);

        if ($this->integrations->isEnabled('mail')
            && $payload->rule->hasChannel(NotificationChannelType::Email)
            && $payload->recipient->isNotificationChannelEnabled($payload->rule->getEventName(), NotificationChannelType::Email, $payload->rule->getChannels())
        ) {
            $this->send($payload);
        }

        return $payload;
    }

    public function send(SendNotificationCommand $command): void
    {
        $this->mailer->sendTemplate(
            [$command->recipient->getEmail()],
            $command->emailSubject,
            $command->emailTemplate,
            $command->templateContext,
        );
    }
}
