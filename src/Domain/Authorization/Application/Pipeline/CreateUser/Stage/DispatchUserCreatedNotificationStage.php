<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\CreateUser\Stage;

use App\Domain\Authorization\Application\Pipeline\CreateUser\CreateUserCommand;
use App\Domain\Notifications\Application\NotificationDispatcher;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.user.create', attributes: ['priority' => -100])]
final readonly class DispatchUserCreatedNotificationStage implements PipelineHandlerInterface
{
    public function __construct(private NotificationDispatcher $dispatcher) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateUserCommand);
        assert($payload->result !== null);

        $user = $payload->result;

        $this->dispatcher->dispatch(
            eventName: 'user.created',
            emailSubject: sprintf('New user created: %s', $user->getFullName()),
            emailTemplate: 'notifications/user_created.html.twig',
            smsText: sprintf('New user %s (%s) has been added to WebDream Manager.', $user->getFullName(), $user->getEmail()),
            templateContext: ['user' => $user],
        );

        return $payload;
    }
}
