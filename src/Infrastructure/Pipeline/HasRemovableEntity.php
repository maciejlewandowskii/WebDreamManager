<?php

declare(strict_types=1);

namespace App\Infrastructure\Pipeline;

interface HasRemovableEntity
{
    public function getEntityToRemove(): object;
}
