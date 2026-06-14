<?php

declare(strict_types=1);

namespace App\UI\Component\Invoicing;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Invoicing\Application\Data\QuoteFormData;
use App\Domain\Invoicing\Application\Pipeline\CreateQuote\CreateQuoteCommand;
use App\Domain\Invoicing\Application\Pipeline\UpdateQuote\UpdateQuoteCommand;
use App\Domain\Invoicing\Entity\QuoteItem;
use App\Domain\Invoicing\Repository\QuoteRepositoryInterface;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineProcessor;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class QuoteEditor
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?string $quoteId = null;

    #[LiveProp(writable: true, onUpdated: 'updatedCustomerId')]
    public string $customerId = '';

    #[LiveProp(writable: true)]
    public string $projectId = '';

    #[LiveProp(writable: true)]
    public string $issuedAt = '';

    #[LiveProp(writable: true)]
    public string $validUntil = '';

    #[LiveProp(writable: true)]
    public string $currency = 'PLN';

    #[LiveProp(writable: true)]
    public string $defaultTaxRate = '23';

    #[LiveProp(writable: true)]
    public string $notes = '';

    #[LiveProp(writable: true)]
    public string $introText = '';

    /** @var array<int, array{description: string, quantity: string, unit: string, unitPrice: string, taxRate: string}> */
    #[LiveProp(writable: true)]
    public array $items = [];

    public function __construct(
        private readonly QuoteRepositoryInterface $quoteRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $router,
        #[AutowireIterator('app.quote.create')] private readonly iterable $createHandlers,
        #[AutowireIterator('app.quote.update')] private readonly iterable $updateHandlers,
    ) {
    }

    public function mount(?string $quoteId = null): void
    {
        if ($quoteId !== null) {
            $this->quoteId = $quoteId;
            $quote = $this->quoteRepository->findById($quoteId);
            if ($quote !== null) {
                $this->customerId     = $quote->getCustomer()->getId();
                $this->projectId      = $quote->getProject()?->getId() ?? '';
                $this->issuedAt       = $quote->getIssuedAt()->format('Y-m-d');
                $this->validUntil     = ($quote->getValidUntil() ?? new \DateTimeImmutable())->format('Y-m-d');
                $this->currency       = $quote->getCurrency();
                $this->defaultTaxRate = $quote->getDefaultTaxRate();
                $this->notes          = $quote->getNotes() ?? '';
                $this->introText      = $quote->getIntroText() ?? '';
                $this->items = array_values($quote->getItems()->map(
                    static fn (QuoteItem $i) => [
                        'description' => $i->getDescription(),
                        'quantity'    => $i->getQuantity(),
                        'unit'        => $i->getUnit(),
                        'unitPrice'   => $i->getUnitPrice(),
                        'taxRate'     => $i->getTaxRate(),
                    ],
                )->toArray());
                return;
            }
        }

        $today = new DateTimeImmutable();
        $this->issuedAt   = $today->format('Y-m-d');
        $this->validUntil = $today->add(new DateInterval('P14D'))->format('Y-m-d');
        $this->items      = [['description' => '', 'quantity' => '1', 'unit' => 'h', 'unitPrice' => '0', 'taxRate' => '23']];
    }

    public function updatedCustomerId(): void
    {
        $rate = $this->resolveHourlyRate();
        $this->items = array_map(
            static fn (array $item) => $item['unit'] === 'h' ? array_merge($item, ['unitPrice' => $rate]) : $item,
            $this->items,
        );
    }

    #[LiveAction]
    public function addItem(): void
    {
        $rate = $this->resolveHourlyRate();
        $this->items[] = ['description' => '', 'quantity' => '1', 'unit' => 'h', 'unitPrice' => $rate, 'taxRate' => $this->defaultTaxRate];
    }

    private function resolveHourlyRate(): string
    {
        if ($this->customerId === '') {
            return '0';
        }
        $customer = $this->customerRepository->findById($this->customerId);

        return ($customer !== null && $customer->getHourlyRate() !== null) ? $customer->getHourlyRate() : '0';
    }

    #[LiveAction]
    public function removeItem(#[LiveArg] int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    #[LiveAction]
    public function save(): RedirectResponse
    {
        $data                 = new QuoteFormData();
        $data->customerId     = $this->customerId;
        $data->projectId      = $this->projectId;
        $data->issuedAt       = $this->issuedAt;
        $data->validUntil     = $this->validUntil;
        $data->currency       = $this->currency;
        $data->defaultTaxRate = $this->defaultTaxRate;
        $data->notes          = $this->notes;
        $data->introText      = $this->introText;
        $data->items          = $this->items;

        if ($this->quoteId !== null) {
            $quote = $this->quoteRepository->findById($this->quoteId);
            if ($quote === null) {
                return new RedirectResponse($this->router->generate('app_quote_index'));
            }
            new PipelineProcessor($this->updateHandlers)->run(new UpdateQuoteCommand($quote, $data));
            $quoteId = $quote->getId();
        } else {
            $command = new CreateQuoteCommand($data);
            new PipelineProcessor($this->createHandlers)->run($command);
            assert($command->result !== null);
            $quoteId = $command->result->getId();
        }

        $session = $this->requestStack->getSession();
        if ($session instanceof \Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('success', 'Quote saved successfully.');
        }

        return new RedirectResponse($this->router->generate('app_quote_show', ['id' => $quoteId]));
    }

    public function getNetTotal(): float
    {
        return array_sum(array_map(
            static fn (array $i) => (float) $i['quantity'] * (float) $i['unitPrice'],
            $this->items,
        ));
    }

    public function getTaxTotal(): float
    {
        return array_sum(array_map(
            static fn (array $i) => (float) $i['quantity'] * (float) $i['unitPrice'] * ((float) $i['taxRate'] / 100),
            $this->items,
        ));
    }

    public function getGrossTotal(): float
    {
        return $this->getNetTotal() + $this->getTaxTotal();
    }

    /** @return Customer[] */
    public function getAvailableCustomers(): array
    {
        return $this->customerRepository->findAll();
    }

    /** @return Project[] */
    public function getAvailableProjects(): array
    {
        if ($this->customerId === '') {
            return [];
        }
        $customer = $this->customerRepository->findById($this->customerId);

        return $customer !== null ? $this->projectRepository->findByCustomer($customer) : [];
    }
}
