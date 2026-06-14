<?php

declare(strict_types=1);

namespace App\Domain\Integration\ValueObject;

final readonly class IntegrationField
{
    public function __construct(
        public string $label,
        public bool $secret = false,
    ) {
    }
}
