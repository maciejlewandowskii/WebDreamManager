<?php

declare(strict_types=1);

namespace App\UI\Component\Customer;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class CustomerList
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $search = '';

    public function __construct(
        private readonly CustomerRepositoryInterface $repository,
    ) {
    }

    /** @return Customer[] */
    public function getCustomers(): array
    {
        if ($this->search !== '') {
            return $this->repository->search($this->search);
        }

        return $this->repository->findAll();
    }
}
