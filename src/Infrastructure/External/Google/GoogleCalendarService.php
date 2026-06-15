<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Google;

use App\Domain\Logging\Application\LoggerService;
use App\Domain\Logging\Entity\LogLevel;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GoogleCalendarService
{
    public function __construct(
        private HttpClientInterface $http,
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri,
        private LoggerService $logger,
    ) {}

    public function getAuthUrl(string $state): string
    {
        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/calendar.events',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => $state,
        ]);
    }

    /** @return array<string, mixed> */
    public function exchangeCode(string $code): array
    {
        $response = $this->http->request('POST', 'https://oauth2.googleapis.com/token', [
            'body' => [
                'code'          => $code,
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $this->redirectUri,
                'grant_type'    => 'authorization_code',
            ],
        ]);

        $data = $response->toArray(false);

        if (isset($data['error'])) {
            $this->logger->externalService(
                LogLevel::Error,
                'Google OAuth code exchange failed: ' . ($data['error_description'] ?? $data['error']),
                'google',
            );
            throw new RuntimeException('Google OAuth error: ' . ($data['error_description'] ?? $data['error']));
        }

        $this->logger->externalService(LogLevel::Info, 'Google OAuth code exchanged successfully', 'google');

        return $data;
    }

    public function getAccessToken(string $refreshToken): string
    {
        $response = $this->http->request('POST', 'https://oauth2.googleapis.com/token', [
            'body' => [
                'refresh_token' => $refreshToken,
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type'    => 'refresh_token',
            ],
        ]);

        $data = $response->toArray(false);

        if (isset($data['error'])) {
            throw new RuntimeException('Google token refresh error: ' . ($data['error_description'] ?? $data['error']));
        }

        return $data['access_token'];
    }

    /** @return array<string, mixed> */
    public function createMeetEvent(string $accessToken, string $customerName, ?string $customerEmail): array
    {
        $start = new DateTimeImmutable('+30 minutes');
        $end   = $start->modify('+60 minutes');

        $attendees = [];
        if ($customerEmail !== null) {
            $attendees[] = ['email' => $customerEmail];
        }

        $body = [
            'summary'        => 'Meeting with ' . $customerName,
            'start'          => ['dateTime' => $start->format(DateTimeInterface::RFC3339)],
            'end'            => ['dateTime' => $end->format(DateTimeInterface::RFC3339)],
            'conferenceData' => [
                'createRequest' => [
                    'requestId'             => bin2hex(random_bytes(8)),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ],
            'attendees' => $attendees,
        ];

        $response = $this->http->request(
            'POST',
            'https://www.googleapis.com/calendar/v3/calendars/primary/events?conferenceDataVersion=1',
            [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
                'json'    => $body,
            ]
        );

        $data = $response->toArray(false);

        if (isset($data['error'])) {
            $this->logger->externalService(
                LogLevel::Error,
                'Google Calendar event creation failed: ' . ($data['error']['message'] ?? 'unknown'),
                'google',
                'integration',
                ['customer' => $customerName],
            );
            throw new RuntimeException('Google Calendar error: ' . ($data['error']['message'] ?? 'unknown'));
        }

        $this->logger->externalService(
            LogLevel::Info,
            'Google Meet created with ' . $customerName,
            'google',
            'integration',
            ['customer' => $customerName, 'meet_url' => self::extractMeetUrl($data)],
        );

        return $data;
    }

    /** @param array<string, mixed> $event */
    public static function extractMeetUrl(array $event): ?string
    {
        $conferenceData = $event['conferenceData'] ?? null;
        if (is_array($conferenceData)) {
            $entryPoints = $conferenceData['entryPoints'] ?? null;
            if (is_array($entryPoints)) {
                foreach ($entryPoints as $ep) {
                    if (is_array($ep) && ($ep['entryPointType'] ?? '') === 'video' && is_string($ep['uri'] ?? null)) {
                        return $ep['uri'];
                    }
                }
            }
        }

        return is_string($event['hangoutLink'] ?? null) ? $event['hangoutLink'] : null;
    }
}
