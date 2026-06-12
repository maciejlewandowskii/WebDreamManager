<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Port;

use App\Domain\Notifications\Application\Pipeline\SendNotification\SendNotificationCommand;
use App\Domain\Notifications\Entity\NotificationChannelType;

interface NotificationChannelInterface
{
    public function supports(NotificationChannelType $type): bool;

    public function send(SendNotificationCommand $command): void;
}
