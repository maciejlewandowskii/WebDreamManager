<?php

declare(strict_types=1);

namespace App\UI\EventListener;

use App\Domain\Identity\Entity\User;
use App\Domain\Logging\Application\LoggerService;
use App\Domain\Logging\Entity\LogLevel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final readonly class SecurityEventListener
{
    public function __construct(private LoggerService $logger) {}

    #[AsEventListener]
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getAuthenticatedToken()->getUser();

        if ($user instanceof User) {
            $this->logger->userAction(
                LogLevel::Info,
                'User logged in: ' . $user->getEmail(),
                $user->getId(),
                $user->getFullName(),
                'security',
                ['email' => $user->getEmail()],
            );
        }
    }

    #[AsEventListener]
    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $this->logger->system(
            LogLevel::Warning,
            'Login attempt failed: ' . $event->getException()->getMessage(),
            'security',
        );
    }

    #[AsEventListener]
    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();

        if ($user instanceof User) {
            $this->logger->userAction(
                LogLevel::Info,
                'User logged out: ' . $user->getEmail(),
                $user->getId(),
                $user->getFullName(),
                'security',
                ['email' => $user->getEmail()],
            );
        }
    }
}
