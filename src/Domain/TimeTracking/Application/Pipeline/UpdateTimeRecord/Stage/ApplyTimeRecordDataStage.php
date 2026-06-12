<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Pipeline\UpdateTimeRecord\Stage;

use App\Domain\TimeTracking\Application\Pipeline\UpdateTimeRecord\UpdateTimeRecordCommand;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.time_record.update', attributes: ['priority' => 200])]
final class ApplyTimeRecordDataStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateTimeRecordCommand);

        $payload->data->applyTo($payload->record);

        return $payload;
    }
}
