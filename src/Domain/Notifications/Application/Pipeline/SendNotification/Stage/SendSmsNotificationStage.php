<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Application\Pipeline\SendNotification\Stage;

use App\Domain\Integration\Application\IntegrationStatusService;
use App\Domain\Notifications\Application\Pipeline\SendNotification\SendNotificationCommand;
use App\Domain\Notifications\Entity\NotificationChannelType;
use App\Domain\Notifications\Port\NotificationChannelInterface;
use App\Infrastructure\Communications\Port\SmsSenderInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Throwable;

#[AutoconfigureTag('app.notifications.send', attributes: ['priority' => 50])]
final readonly class SendSmsNotificationStage implements PipelineHandlerInterface, NotificationChannelInterface
{
    public function __construct(
        private SmsSenderInterface $smsSender,
        private LoggerInterface $logger,
        private IntegrationStatusService $integrations,
    ) {
    }

    public function supports(NotificationChannelType $type): bool
    {
        return $type === NotificationChannelType::Sms;
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SendNotificationCommand);

        if ($this->integrations->isEnabled('sms')
            && $payload->rule->hasChannel(NotificationChannelType::Sms)
            && $this->smsSender->isConfigured()
            && $payload->recipient->isNotificationChannelEnabled($payload->rule->getEventName(), NotificationChannelType::Sms, $payload->rule->getChannels())
        ) {
            $this->send($payload);
        }

        return $payload;
    }

    public function send(SendNotificationCommand $command): void
    {
        $phone = $command->recipient->getPhone();

        if ($phone === null) {
            return;
        }

        try {
            $this->smsSender->send($phone, $command->smsText);
        } catch (Throwable $e) {
            $this->logger->error('SMS notification failed', [
                'recipient' => $command->recipient->getEmail(),
                'event' => $command->rule->getEventName(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
