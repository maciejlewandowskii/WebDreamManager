<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateQuote;

use App\Domain\Invoicing\Application\Data\QuoteFormData;
use App\Domain\Invoicing\Entity\Quote;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class CreateQuoteCommand implements PipelineCommandInterface
{
    public ?Quote $result = null;

    public function __construct(public readonly QuoteFormData $data)
    {
    }

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
