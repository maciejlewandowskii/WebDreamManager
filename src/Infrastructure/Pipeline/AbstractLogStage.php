<?php

declare(strict_types=1);

namespace App\Infrastructure\Pipeline;

use App\Domain\Identity\Entity\User;
use App\Domain\Logging\Application\LoggerService;
use App\Domain\Logging\Entity\LogLevel;
use Symfony\Bundle\SecurityBundle\Security;

abstract readonly class AbstractLogStage implements PipelineHandlerInterface
{
    public function __construct(
        protected LoggerService $logger,
        protected Security $security,
    ) {}

    protected function currentUserId(): ?string
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $user->getId() : null;
    }

    protected function currentUserName(): ?string
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $user->getFullName() : null;
    }

    protected function logUserAction(string $message, string $category = 'app', array $context = []): void
    {
        $this->logger->userAction(
            LogLevel::Info,
            $message,
            $this->currentUserId(),
            $this->currentUserName(),
            $category,
            $context,
        );
    }

    protected function logSystem(string $message, string $category = 'system', array $context = []): void
    {
        $this->logger->system(LogLevel::Info, $message, $category, $context);
    }
}
