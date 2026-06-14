<?php

declare(strict_types=1);

namespace App\Domain\Integration\Application;

use App\Domain\System\Repository\SystemSettingRepositoryInterface;

final readonly class IntegrationStatusService
{
    public function __construct(
        private SystemSettingRepositoryInterface $settings,
    ) {
    }

    public function isEnabled(string $key): bool
    {
        return $this->settings->get('INTEGRATION_' . strtoupper($key) . '_ENABLED', '0') === '1';
    }
}
