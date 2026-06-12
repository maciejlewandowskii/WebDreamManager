<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\CreateMeeting\Stage;

use App\Domain\Customer\Application\Pipeline\CreateMeeting\CreateMeetingCommand;
use App\Infrastructure\External\Google\GoogleCalendarService;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.customer.meet', attributes: ['priority' => 200])]
final readonly class CreateGoogleMeetStage implements PipelineHandlerInterface
{
    public function __construct(private GoogleCalendarService $google) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateMeetingCommand);

        $event = $this->google->createMeetEvent(
            $payload->accessToken,
            $payload->customer->getName(),
            $payload->customer->getEmail(),
        );

        $payload->meetUrl = GoogleCalendarService::extractMeetUrl($event);

        return $payload;
    }
}
