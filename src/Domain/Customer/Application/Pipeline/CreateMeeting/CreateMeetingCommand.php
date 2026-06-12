<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\CreateMeeting;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Identity\Entity\User;

final class CreateMeetingCommand
{
    public ?string $meetUrl = null;

    public function __construct(
        public readonly Customer $customer,
        public readonly User $organizer,
        public readonly string $accessToken,
    ) {}
}
