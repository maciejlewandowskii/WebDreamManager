<?php

declare(strict_types=1);

namespace App\Domain\System\Application\Pipeline\TriggerWatchtowerUpdate;

final class TriggerWatchtowerUpdateCommand
{
    public bool $triggered = false;

    public function __construct(
        public readonly string $url,
        public readonly string $token,
    ) {
    }
}
