<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateInvoiceFromTimeTracking;

use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Project\Entity\Project;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class CreateInvoiceFromTimeTrackingCommand implements PipelineCommandInterface
{
    public ?Invoice $result = null;

    /** @param string[] $recordIds */
    public function __construct(
        public readonly Project $project,
        public readonly array $recordIds,
    ) {}

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
