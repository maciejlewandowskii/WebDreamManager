<?php

declare(strict_types=1);

namespace App\Domain\System\Application\Pipeline\TriggerWatchtowerUpdate\Stage;

use App\Domain\Logging\Entity\LogLevel;
use App\Domain\System\Application\Pipeline\TriggerWatchtowerUpdate\TriggerWatchtowerUpdateCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.system.watchtower_trigger', attributes: ['priority' => -200])]
final readonly class LogWatchtowerTriggerStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof TriggerWatchtowerUpdateCommand);

        $this->logger->system(
            $payload->triggered ? LogLevel::Info : LogLevel::Warning,
            $payload->triggered ? 'Watchtower update triggered successfully' : 'Watchtower update trigger failed',
            'system',
            ['triggered' => $payload->triggered, 'url' => $payload->url],
        );

        return $payload;
    }
}
