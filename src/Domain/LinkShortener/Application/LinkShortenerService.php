<?php

declare(strict_types=1);

namespace App\Domain\LinkShortener\Application;

use App\Domain\LinkShortener\Entity\ShortLink;
use App\Domain\LinkShortener\Repository\ShortLinkRepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class LinkShortenerService
{
    private const int CODE_LENGTH = 7;

    /** Unambiguous alphabet — excludes 0/O and 1/l/I to avoid visually confusing codes. */
    private const string ALPHABET = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function __construct(
        private ShortLinkRepositoryInterface $repository,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function shorten(string $targetUrl, ?string $sourceType = null, ?string $sourceLabel = null): string
    {
        $shortLink = new ShortLink($this->generateUniqueCode(), $targetUrl, $sourceType, $sourceLabel);
        $this->repository->save($shortLink);

        return $this->resolveShortUrl($shortLink);
    }

    public function resolveShortUrl(ShortLink $shortLink): string
    {
        return $this->urlGenerator->generate(
            'app_short_link_redirect',
            ['code' => $shortLink->getCode()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = $this->randomCode();
        } while ($this->repository->codeExists($code));

        return $code;
    }

    private function randomCode(): string
    {
        $alphabetLength = strlen(self::ALPHABET);
        $code           = '';

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $code;
    }
}
