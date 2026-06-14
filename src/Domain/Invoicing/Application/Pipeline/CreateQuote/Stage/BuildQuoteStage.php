<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateQuote\Stage;

use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Invoicing\Application\Pipeline\CreateQuote\CreateQuoteCommand;
use App\Domain\Invoicing\Entity\Quote;
use App\Domain\Invoicing\Entity\QuoteItem;
use App\Domain\Invoicing\Repository\QuoteRepositoryInterface;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.create', attributes: ['priority' => 200])]
final class BuildQuoteStage implements PipelineHandlerInterface
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly QuoteRepositoryInterface $quoteRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateQuoteCommand);

        $customer = $this->customerRepository->findById($payload->data->customerId);
        if ($customer === null) {
            throw new RuntimeException('Customer not found.');
        }

        $quote = new Quote($this->quoteRepository->getNextNumber(), $customer);
        $quote->setCurrency($payload->data->currency);
        $quote->setDefaultTaxRate($payload->data->defaultTaxRate);
        $quote->setNotes($payload->data->notes !== '' ? $payload->data->notes : null);
        $quote->setIntroText($payload->data->introText !== '' ? $payload->data->introText : null);

        $issuedAt   = DateTimeImmutable::createFromFormat('Y-m-d', $payload->data->issuedAt);
        $validUntil = DateTimeImmutable::createFromFormat('Y-m-d', $payload->data->validUntil);
        if ($issuedAt !== false) {
            $quote->setIssuedAt($issuedAt);
        }
        if ($validUntil !== false) {
            $quote->setValidUntil($validUntil);
        }

        if ($payload->data->projectId !== '') {
            $quote->setProject($this->projectRepository->findById($payload->data->projectId));
        }

        foreach ($payload->data->items as $i => $itemData) {
            $item = new QuoteItem($quote);
            $item->setDescription($itemData['description']);
            $item->setQuantity($itemData['quantity']);
            $item->setUnit($itemData['unit']);
            $item->setUnitPrice($itemData['unitPrice']);
            $item->setTaxRate($itemData['taxRate']);
            $item->setSortOrder($i);
            $quote->addItem($item);
        }

        $payload->result = $quote;

        return $payload;
    }
}
