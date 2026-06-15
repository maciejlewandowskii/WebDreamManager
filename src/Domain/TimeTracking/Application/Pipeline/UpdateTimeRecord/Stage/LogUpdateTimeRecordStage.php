<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Pipeline\UpdateTimeRecord\Stage;

use App\Domain\TimeTracking\Application\Pipeline\UpdateTimeRecord\UpdateTimeRecordCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.time_record.update', attributes: ['priority' => -200])]
final readonly class LogUpdateTimeRecordStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateTimeRecordCommand);

        $this->logUserAction(
            "Time record updated: {$payload->record->getSpentHours()}h on {$payload->record->getProject()->getName()}",
            'time',
            ['id' => $payload->record->getId(), 'project' => $payload->record->getProject()->getName(), 'hours' => $payload->record->getSpentHours(), 'date' => $payload->record->getDate()->format('Y-m-d')],
        );

        return $payload;
    }
}
