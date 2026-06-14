<?php

declare(strict_types=1);

namespace App\Domain\Integration\Application\Pipeline\SaveIntegrationSettings;

use App\Domain\Integration\Port\IntegrationInterface;

final readonly class SaveIntegrationSettingsCommand
{
    /**
     * @param array<string, string> $values Setting key → submitted value (empty string means "unchanged" for secrets)
     */
    public function __construct(
        public IntegrationInterface $integration,
        public bool $enabled,
        public array $values,
    ) {
    }
}
