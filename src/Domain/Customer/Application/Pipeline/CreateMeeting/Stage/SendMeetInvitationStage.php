<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\CreateMeeting\Stage;

use App\Domain\Customer\Application\Pipeline\CreateMeeting\CreateMeetingCommand;
use App\Infrastructure\Communications\Port\EmailSenderInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.customer.meet', attributes: ['priority' => 100])]
final readonly class SendMeetInvitationStage implements PipelineHandlerInterface
{
    public function __construct(private EmailSenderInterface $mailer) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateMeetingCommand);

        if ($payload->meetUrl === null || $payload->customer->getEmail() === null) {
            return $payload;
        }

        $this->mailer->sendTemplate(
            [$payload->customer->getEmail()],
            'Meeting invitation — ' . $payload->customer->getName(),
            'views/customer/email/meet_invitation.html.twig',
            [
                'customer'    => $payload->customer,
                'meet_url'    => $payload->meetUrl,
                'organizer'   => $payload->organizer,
                'event_title' => 'Meeting with ' . $payload->customer->getName(),
                'event_start' => null,
            ],
        );

        return $payload;
    }
}
