<?php

declare(strict_types=1);

namespace App\Domain\System\Repository;

use App\Domain\System\Entity\SystemSetting;

interface SystemSettingRepositoryInterface
{
    public function get(string $key, ?string $default = null): ?string;

    public function set(string $key, ?string $value): void;

    /** @return SystemSetting[] */
    public function all(): array;
}
