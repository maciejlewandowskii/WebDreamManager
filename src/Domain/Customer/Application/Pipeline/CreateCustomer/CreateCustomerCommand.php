<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\CreateCustomer;

use App\Domain\Customer\Application\Data\CustomerData;
use App\Domain\Customer\Entity\Customer;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class CreateCustomerCommand implements PipelineCommandInterface
{
    public ?Customer $result = null;

    public function __construct(public readonly CustomerData $data)
    {
    }

    public static function fromData(CustomerData $data): self
    {
        return new self($data);
    }

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
