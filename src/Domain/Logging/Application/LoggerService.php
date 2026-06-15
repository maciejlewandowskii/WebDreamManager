<?php

declare(strict_types=1);

namespace App\Domain\Logging\Application;

use App\Domain\Logging\Application\Data\LogEntryData;
use App\Domain\Logging\Application\Pipeline\CreateLogEntry\CreateLogEntryCommand;
use App\Domain\Logging\Entity\LogLevel;
use App\Domain\Logging\Entity\LogType;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use App\Infrastructure\Pipeline\PipelineProcessor;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class LoggerService
{
    /** @param iterable<PipelineHandlerInterface> $createHandlers */
    public function __construct(
        #[AutowireIterator('app.logging.create')] private iterable $createHandlers,
        private RequestStack $requestStack,
    ) {
    }

    /** @param array<string, mixed> $context */
    public function system(LogLevel $level, string $message, string $category = 'system', array $context = []): void
    {
        $data           = new LogEntryData();
        $data->type     = LogType::SystemLog;
        $data->level    = $level;
        $data->message  = $message;
        $data->category = $category;
        $data->context  = $context !== [] ? $context : null;
        $data->ipAddress = $this->resolveIp();

        $this->dispatch($data);
    }

    /** @param array<string, mixed> $context */
    public function userAction(
        LogLevel $level,
        string $message,
        ?string $userId = null,
        ?string $userName = null,
        string $category = 'app',
        array $context = [],
    ): void {
        $data           = new LogEntryData();
        $data->type     = LogType::UserAction;
        $data->level    = $level;
        $data->message  = $message;
        $data->category = $category;
        $data->context  = $context !== [] ? $context : null;
        $data->userId   = $userId;
        $data->userName = $userName;
        $data->ipAddress = $this->resolveIp();

        $this->dispatch($data);
    }

    /** @param array<string, mixed> $context */
    public function externalService(
        LogLevel $level,
        string $message,
        string $service,
        string $category = 'integration',
        array $context = [],
    ): void {
        $data           = new LogEntryData();
        $data->type     = LogType::ExternalService;
        $data->level    = $level;
        $data->message  = $message;
        $data->category = $category;
        $data->service  = $service;
        $data->context  = $context !== [] ? $context : null;
        $data->ipAddress = $this->resolveIp();

        $this->dispatch($data);
    }

    private function dispatch(LogEntryData $data): void
    {
        new PipelineProcessor($this->createHandlers)->run(CreateLogEntryCommand::fromData($data));
    }

    private function resolveIp(): ?string
    {
        return $this->requestStack->getMainRequest()?->getClientIp();
    }
}
