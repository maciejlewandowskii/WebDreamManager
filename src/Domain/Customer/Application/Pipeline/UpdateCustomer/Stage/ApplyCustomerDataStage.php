<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\UpdateCustomer\Stage;

use App\Domain\Customer\Application\Pipeline\UpdateCustomer\UpdateCustomerCommand;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.customer.update', attributes: ['priority' => 200])]
final class ApplyCustomerDataStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateCustomerCommand);

        $payload->data->applyTo($payload->customer);

        return $payload;
    }
}
