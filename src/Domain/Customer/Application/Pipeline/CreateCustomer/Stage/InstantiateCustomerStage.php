<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\CreateCustomer\Stage;

use App\Domain\Customer\Application\Pipeline\CreateCustomer\CreateCustomerCommand;
use App\Domain\Customer\Entity\Customer;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.customer.create', attributes: ['priority' => 200])]
final class InstantiateCustomerStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateCustomerCommand);

        $customer = new Customer($payload->data->name);
        $payload->data->applyTo($customer);
        $payload->result = $customer;

        return $payload;
    }
}
