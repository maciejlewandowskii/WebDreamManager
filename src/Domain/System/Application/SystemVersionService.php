<?php

declare(strict_types=1);

namespace App\Domain\System\Application;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final readonly class SystemVersionService
{
    public function __construct(
        #[Autowire(env: 'APP_VERSION')] private string $currentVersion,
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        #[Autowire(param: 'app.github_repo')] private string $githubRepo,
    ) {
    }

    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    public function getLatestVersion(): ?string
    {
        try {
            return $this->cache->get('system.latest_version', function (ItemInterface $item): ?string {
                $item->expiresAfter(3600);

                return $this->fetchLatestVersionFromGithub();
            });
        } catch (Throwable) {
            return null;
        }
    }

    public function isUpdateAvailable(): bool
    {
        $latest = $this->getLatestVersion();

        if ($latest === null || $this->currentVersion === 'dev') {
            return false;
        }

        return version_compare(ltrim($latest, 'v'), ltrim($this->currentVersion, 'v'), '>');
    }

    public function invalidateCache(): void
    {
        try {
            $this->cache->delete('system.latest_version');
        } catch (Throwable) {
        }
    }

    private function fetchLatestVersionFromGithub(): ?string
    {
        if ($this->githubRepo === '') {
            return null;
        }

        try {
            $response = $this->httpClient->request(
                'GET',
                'https://api.github.com/repos/' . $this->githubRepo . '/releases/latest',
                [
                    'headers' => ['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'],
                    'timeout' => 5,
                ],
            );

            $data = $response->toArray();
        } catch (Throwable) {
            return null;
        }

        return isset($data['tag_name']) && is_string($data['tag_name']) ? $data['tag_name'] : null;
    }
}
