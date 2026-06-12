<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\SendQuoteEmail;

use App\Domain\Invoicing\Entity\Quote;

final readonly class SendQuoteEmailCommand
{
    public function __construct(
        public Quote $quote,
        public ?string $pdfId,
    ) {}
}
