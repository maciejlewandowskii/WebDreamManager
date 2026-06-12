<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\UpdateQuote;

use App\Domain\Invoicing\Application\Data\QuoteFormData;
use App\Domain\Invoicing\Entity\Quote;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final readonly class UpdateQuoteCommand implements PipelineCommandInterface
{
    public function __construct(
        public Quote $quote,
        public QuoteFormData $data,
    ) {
    }

    public function getEntityToSave(): object
    {
        return $this->quote;
    }
}
