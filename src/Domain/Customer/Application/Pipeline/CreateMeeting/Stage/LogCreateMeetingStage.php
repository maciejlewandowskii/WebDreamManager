<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\CreateMeeting\Stage;

use App\Domain\Customer\Application\Pipeline\CreateMeeting\CreateMeetingCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.customer.meet', attributes: ['priority' => -200])]
final readonly class LogCreateMeetingStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateMeetingCommand);

        $this->logUserAction(
            "Google Meet scheduled with {$payload->customer->getName()}",
            'customers',
            ['customer_id' => $payload->customer->getId(), 'meet_url' => $payload->meetUrl],
        );

        return $payload;
    }
}
