<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\GenerateQuotePdf;

use App\Domain\Invoicing\Entity\Quote;
use App\Domain\Invoicing\Entity\QuotePdfRecord;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class GenerateQuotePdfCommand implements PipelineCommandInterface
{
    public ?QuotePdfRecord $result = null;

    public function __construct(public readonly Quote $quote) {}

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
