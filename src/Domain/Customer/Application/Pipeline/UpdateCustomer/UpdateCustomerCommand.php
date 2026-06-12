<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\UpdateCustomer;

use App\Domain\Customer\Application\Data\CustomerData;
use App\Domain\Customer\Entity\Customer;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final readonly class UpdateCustomerCommand implements PipelineCommandInterface
{
    public function __construct(
        public Customer $customer,
        public CustomerData $data,
    ) {
    }

    public function getEntityToSave(): object
    {
        return $this->customer;
    }
}
