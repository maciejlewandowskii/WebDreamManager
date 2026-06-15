<?php

declare(strict_types=1);

namespace App\Domain\Logging\Application\Data;

use App\Domain\Logging\Entity\LogLevel;
use App\Domain\Logging\Entity\LogType;

final class LogEntryData
{
    public LogType $type;
    public LogLevel $level;
    public string $category = 'app';
    public string $message = '';
    public ?array $context = null;
    public ?string $userId = null;
    public ?string $userName = null;
    public ?string $service = null;
    public ?string $ipAddress = null;
    public ?string $requestId = null;
}
