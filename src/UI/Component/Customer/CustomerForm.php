<?php

declare(strict_types=1);

namespace App\UI\Component\Customer;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Entity\CustomerStatus;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[AsLiveComponent]
final class CustomerForm
{
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    #[LiveProp(writable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 200)]
    public string $name = '';

    #[LiveProp(writable: true)]
    #[Assert\Email]
    public string $email = '';

    #[LiveProp(writable: true)]
    public string $phone = '';

    #[LiveProp(writable: true)]
    public string $company = '';

    #[LiveProp(writable: true)]
    public string $taxId = '';

    #[LiveProp(writable: true)]
    public string $notes = '';

    #[LiveProp]
    public ?string $customerId = null;

    private ?Customer $existingCustomer = null;

    public function __construct(
        private readonly CustomerRepositoryInterface $repository,
    ) {
    }

    public function mount(?string $customerId = null): void
    {
        if ($customerId !== null) {
            $this->customerId = $customerId;
            $customer = $this->repository->findById($customerId);
            if ($customer !== null) {
                $this->existingCustomer = $customer;
                $this->name    = $customer->getName();
                $this->email   = $customer->getEmail() ?? '';
                $this->phone   = $customer->getPhone() ?? '';
                $this->company = $customer->getCompany() ?? '';
                $this->taxId   = $customer->getTaxId() ?? '';
                $this->notes   = $customer->getNotes() ?? '';
            }
        }
    }

    #[LiveAction]
    public function save(): void
    {
        $this->validate();

        if ($this->customerId !== null && $this->existingCustomer !== null) {
            $customer = $this->existingCustomer;
        } else {
            $customer = new Customer($this->name);
        }

        $customer->setName($this->name);
        $customer->setEmail($this->email !== '' ? $this->email : null);
        $customer->setPhone($this->phone !== '' ? $this->phone : null);
        $customer->setCompany($this->company !== '' ? $this->company : null);
        $customer->setTaxId($this->taxId !== '' ? $this->taxId : null);
        $customer->setNotes($this->notes !== '' ? $this->notes : null);
        $customer->setStatus(CustomerStatus::Active);

        $this->repository->save($customer);
    }
}
