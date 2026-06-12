<?php

declare(strict_types=1);

namespace App\Infrastructure\Pipeline;

interface PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed;
}
