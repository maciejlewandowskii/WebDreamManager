<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Pipeline\CreateTimeRecord\Stage;

use App\Domain\TimeTracking\Application\Pipeline\CreateTimeRecord\CreateTimeRecordCommand;
use App\Domain\TimeTracking\Entity\TimeRecord;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.time_record.create', attributes: ['priority' => 200])]
final class InstantiateTimeRecordStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateTimeRecordCommand);
        assert($payload->data->project !== null);

        $record = new TimeRecord($payload->data->title, $payload->data->project, $payload->user);
        $payload->data->applyTo($record);
        $payload->result = $record;

        return $payload;
    }
}
