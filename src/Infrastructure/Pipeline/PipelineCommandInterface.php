<?php

declare(strict_types=1);

namespace App\Infrastructure\Pipeline;

interface PipelineCommandInterface
{
    public function getEntityToSave(): object;
}
