<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\DeleteCustomer;

use App\Domain\Customer\Entity\Customer;
use App\Infrastructure\Pipeline\HasRemovableEntity;

final readonly class DeleteCustomerCommand implements HasRemovableEntity
{
    public function __construct(public Customer $customer)
    {
    }

    public function getEntityToRemove(): object
    {
        return $this->customer;
    }
}
