<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\DeleteQuote;

use App\Domain\Invoicing\Entity\Quote;
use App\Infrastructure\Pipeline\HasRemovableEntity;

final readonly class DeleteQuoteCommand implements HasRemovableEntity
{
    public function __construct(public Quote $quote)
    {
    }

    public function getEntityToRemove(): object
    {
        return $this->quote;
    }
}
