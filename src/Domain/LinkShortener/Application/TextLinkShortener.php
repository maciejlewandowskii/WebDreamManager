<?php

declare(strict_types=1);

namespace App\Domain\LinkShortener\Application;

use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\Quote;

final readonly class TextLinkShortener
{
    private const string URL_PATTERN = '/\bhttps?:\/\/[^\s<>"\')]+/i';

    /** Matches a URL whose path is already one of our own short links (e.g. https://host/s/aB3dE9f). */
    private const string ALREADY_SHORTENED_PATTERN = '#/s/[A-Za-z0-9]{4,12}/?$#';

    public function __construct(private LinkShortenerService $shortener)
    {
    }

    public function shortenInvoiceLinks(Invoice $invoice): void
    {
        $sourceLabel = $invoice->getNumber();

        $invoice->setNotes($this->shorten($invoice->getNotes(), 'invoice', $sourceLabel));
        $invoice->setPaymentTerms($this->shorten($invoice->getPaymentTerms(), 'invoice', $sourceLabel));

        foreach ($invoice->getItems() as $item) {
            $item->setDescription((string) $this->shorten($item->getDescription(), 'invoice', $sourceLabel));
        }
    }

    public function shortenQuoteLinks(Quote $quote): void
    {
        $sourceLabel = $quote->getNumber();

        $quote->setNotes($this->shorten($quote->getNotes(), 'quote', $sourceLabel));
        $quote->setIntroText($this->shorten($quote->getIntroText(), 'quote', $sourceLabel));

        foreach ($quote->getItems() as $item) {
            $item->setDescription((string) $this->shorten($item->getDescription(), 'quote', $sourceLabel));
        }
    }

    private function shorten(?string $text, string $sourceType, string $sourceLabel): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        /** @var array<string, string> $cache */
        $cache = [];

        $replaced = preg_replace_callback(
            self::URL_PATTERN,
            function (array $matches) use (&$cache, $sourceType, $sourceLabel): string {
                $url      = $matches[0];
                $trailing = '';

                while ($url !== '' && str_contains('.,;:!?)', $url[-1])) {
                    $trailing = $url[-1] . $trailing;
                    $url      = substr($url, 0, -1);
                }

                if ($url === '' || preg_match(self::ALREADY_SHORTENED_PATTERN, $url) === 1) {
                    return $matches[0];
                }

                if (!isset($cache[$url])) {
                    $cache[$url] = $this->shortener->shorten($url, $sourceType, $sourceLabel);
                }

                return $cache[$url] . $trailing;
            },
            $text,
        );

        return $replaced ?? $text;
    }
}
