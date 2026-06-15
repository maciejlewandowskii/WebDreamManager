<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Application\Pipeline\CreateTimeRecord\Stage;

use App\Domain\TimeTracking\Application\Pipeline\CreateTimeRecord\CreateTimeRecordCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.time_record.create', attributes: ['priority' => -200])]
final readonly class LogCreateTimeRecordStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateTimeRecordCommand);
        assert($payload->result !== null);

        $this->logUserAction(
            "Time record created: {$payload->result->getSpentHours()}h on {$payload->result->getProject()->getName()}",
            'time',
            ['id' => $payload->result->getId(), 'project' => $payload->result->getProject()->getName(), 'hours' => $payload->result->getSpentHours(), 'date' => $payload->result->getDate()->format('Y-m-d')],
        );

        return $payload;
    }
}
