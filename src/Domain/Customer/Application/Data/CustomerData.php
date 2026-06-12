<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Data;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Entity\CustomerStatus;
use App\Domain\Customer\Entity\PdfColorMode;

final class CustomerData
{
    public string $name = '';
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $company = null;
    public ?string $taxId = null;
    public ?string $notes = null;
    public CustomerStatus $status = CustomerStatus::Active;
    /** @var array{amount: ?string, currency: string} */
    public array $hourlyRate = ['amount' => null, 'currency' => 'PLN'];
    public PdfColorMode $pdfColorMode = PdfColorMode::Light;

    public static function fromEntity(Customer $customer): self
    {
        $data = new self();
        $data->name         = $customer->getName();
        $data->email        = $customer->getEmail();
        $data->phone        = $customer->getPhone();
        $data->company      = $customer->getCompany();
        $data->taxId        = $customer->getTaxId();
        $data->notes        = $customer->getNotes();
        $data->status       = $customer->getStatus();
        $data->hourlyRate   = ['amount' => $customer->getHourlyRate(), 'currency' => $customer->getHourlyRateCurrency()];
        $data->pdfColorMode = $customer->getPdfColorMode();

        return $data;
    }

    public function applyTo(Customer $customer): void
    {
        $customer->setName($this->name);
        $customer->setEmail($this->email !== '' ? $this->email : null);
        $customer->setPhone($this->phone !== '' ? $this->phone : null);
        $customer->setCompany($this->company !== '' ? $this->company : null);
        $customer->setTaxId($this->taxId !== '' ? $this->taxId : null);
        $customer->setNotes($this->notes !== '' ? $this->notes : null);
        $customer->setStatus($this->status);
        $amount = $this->hourlyRate['amount'] ?? null;
        $customer->setHourlyRate($amount !== '' && $amount !== null ? (string) $amount : null);
        $customer->setHourlyRateCurrency($this->hourlyRate['currency'] ?? 'PLN');
        $customer->setPdfColorMode($this->pdfColorMode);
    }
}
