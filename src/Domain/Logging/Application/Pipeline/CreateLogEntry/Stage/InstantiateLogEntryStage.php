<?php

declare(strict_types=1);

namespace App\Domain\Logging\Application\Pipeline\CreateLogEntry\Stage;

use App\Domain\Logging\Application\Pipeline\CreateLogEntry\CreateLogEntryCommand;
use App\Domain\Logging\Entity\LogEntry;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.logging.create', attributes: ['priority' => 100])]
final class InstantiateLogEntryStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateLogEntryCommand);

        $data = $payload->data;

        $payload->result = new LogEntry(
            type:      $data->type,
            level:     $data->level,
            category:  $data->category,
            message:   $data->message,
            context:   $data->context,
            userId:    $data->userId,
            userName:  $data->userName,
            service:   $data->service,
            ipAddress: $data->ipAddress,
            requestId: $data->requestId,
        );

        return $payload;
    }
}
