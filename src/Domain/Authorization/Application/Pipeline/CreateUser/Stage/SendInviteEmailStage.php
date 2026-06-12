<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\CreateUser\Stage;

use App\Domain\Authorization\Application\Pipeline\CreateUser\CreateUserCommand;
use App\Infrastructure\Communications\Port\EmailSenderInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AutoconfigureTag('app.authorization.user.create', attributes: ['priority' => 50])]
final class SendInviteEmailStage implements PipelineHandlerInterface
{
    public function __construct(
        private readonly EmailSenderInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateUserCommand);
        assert($payload->result !== null);

        $user = $payload->result;
        $token = $user->getSetupToken();

        if ($token === null) {
            return $payload;
        }

        $setupUrl = $this->urlGenerator->generate(
            'app_account_setup',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $this->mailer->sendTemplate(
            [$user->getEmail()],
            'Set up your WebDream Manager account',
            'emails/account_invite.html.twig',
            [
                'user'     => $user,
                'setupUrl' => $setupUrl,
            ],
        );

        return $payload;
    }
}
