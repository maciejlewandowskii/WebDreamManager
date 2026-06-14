<?php

declare(strict_types=1);

namespace App\Domain\System\Application\Pipeline\TriggerWatchtowerUpdate\Stage;

use App\Domain\System\Application\Pipeline\TriggerWatchtowerUpdate\TriggerWatchtowerUpdateCommand;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AutoconfigureTag('app.system.watchtower_trigger', attributes: ['priority' => 100])]
final readonly class CallWatchtowerApiStage implements PipelineHandlerInterface
{
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof TriggerWatchtowerUpdateCommand);

        try {
            $response = $this->httpClient->request('GET', rtrim($payload->url, '/') . '/v1/update', [
                'headers' => ['Authorization' => 'Bearer ' . $payload->token],
                'timeout' => 10,
            ]);

            $payload->triggered = $response->getStatusCode() === 200;
        } catch (TransportExceptionInterface) {
            $payload->triggered = false;
        }

        return $payload;
    }
}
