<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Pipeline\DeleteTimeRecord\Stage;

use App\Domain\TimeTracking\Application\Pipeline\DeleteTimeRecord\DeleteTimeRecordCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.time_record.delete', attributes: ['priority' => -200])]
final readonly class LogDeleteTimeRecordStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof DeleteTimeRecordCommand);

        $this->logUserAction(
            "Time record deleted: {$payload->record->getSpentHours()}h on {$payload->record->getProject()->getName()}",
            'time',
            ['id' => $payload->record->getId(), 'project' => $payload->record->getProject()->getName(), 'hours' => $payload->record->getSpentHours(), 'date' => $payload->record->getDate()->format('Y-m-d')],
        );

        return $payload;
    }
}
