<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\UpdateQuote\Stage;

use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Invoicing\Application\Pipeline\UpdateQuote\UpdateQuoteCommand;
use App\Domain\Invoicing\Entity\QuoteItem;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.update', attributes: ['priority' => 200])]
final class ApplyQuoteDataStage implements PipelineHandlerInterface
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateQuoteCommand);

        $quote = $payload->quote;
        $data  = $payload->data;

        $customer = $this->customerRepository->findById($data->customerId);
        if ($customer !== null) {
            $quote->setCustomer($customer);
        }

        $quote->setCurrency($data->currency);
        $quote->setDefaultTaxRate($data->defaultTaxRate);
        $quote->setNotes($data->notes !== '' ? $data->notes : null);
        $quote->setIntroText($data->introText !== '' ? $data->introText : null);

        $issuedAt   = DateTimeImmutable::createFromFormat('Y-m-d', $data->issuedAt);
        $validUntil = DateTimeImmutable::createFromFormat('Y-m-d', $data->validUntil);
        if ($issuedAt !== false) {
            $quote->setIssuedAt($issuedAt);
        }
        if ($validUntil !== false) {
            $quote->setValidUntil($validUntil);
        }

        $quote->setProject($data->projectId !== '' ? $this->projectRepository->findById($data->projectId) : null);

        foreach ($quote->getItems()->toArray() as $existingItem) {
            $quote->removeItem($existingItem);
        }

        foreach ($data->items as $i => $itemData) {
            $item = new QuoteItem($quote);
            $item->setDescription($itemData['description']);
            $item->setQuantity($itemData['quantity']);
            $item->setUnit($itemData['unit']);
            $item->setUnitPrice($itemData['unitPrice']);
            $item->setTaxRate($itemData['taxRate']);
            $item->setSortOrder($i);
            $quote->addItem($item);
        }

        return $payload;
    }
}
