<?php

declare(strict_types=1);

namespace App\Infrastructure\Pipeline;

final class PipelineProcessor
{
    public function __construct(private readonly iterable $handlers)
    {
    }

    public function run(mixed $payload): mixed
    {
        foreach ($this->handlers as $handler) {
            $payload = $handler->handle($payload);
        }

        return $payload;
    }
}
