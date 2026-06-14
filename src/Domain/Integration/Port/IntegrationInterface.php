<?php

declare(strict_types=1);

namespace App\Domain\Integration\Port;

use App\Domain\Integration\ValueObject\IntegrationField;

interface IntegrationInterface
{
    public function getKey(): string;

    public function getName(): string;

    public function getDescription(): string;

    public function getIcon(): string;

    /** @return array<string, IntegrationField> */
    public function getFields(): array;
}
