<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger\Notification;

use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Domain\Notifications\Application\Pipeline\SendNotification\SendNotificationCommand;
use App\Domain\Notifications\Repository\NotificationRuleRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineProcessor;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DispatchNotificationHandler
{
    public function __construct(
        private readonly NotificationRuleRepositoryInterface $ruleRepository,
        private readonly UserRepositoryInterface $userRepository,
        #[AutowireIterator('app.notifications.send')] private readonly iterable $notificationHandlers,
    ) {}

    public function __invoke(DispatchNotificationMessage $message): void
    {
        $rules = $this->ruleRepository->findActiveByEvent($message->eventName);

        foreach ($rules as $rule) {
            $permission = $rule->getRequiredPermission();

            $recipients = $permission !== null
                ? $this->userRepository->findActiveWithPermission($permission)
                : $this->userRepository->findActive();

            foreach ($recipients as $user) {
                $command = new SendNotificationCommand(
                    $rule,
                    $user,
                    $message->emailSubject,
                    $message->emailTemplate,
                    $message->smsText,
                    $message->templateContext,
                );

                new PipelineProcessor($this->notificationHandlers)->run($command);
            }
        }
    }
}
