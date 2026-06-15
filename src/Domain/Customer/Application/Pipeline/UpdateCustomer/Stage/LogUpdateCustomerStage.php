<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\UpdateCustomer\Stage;

use App\Domain\Customer\Application\Pipeline\UpdateCustomer\UpdateCustomerCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.customer.update', attributes: ['priority' => -200])]
final readonly class LogUpdateCustomerStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateCustomerCommand);

        $this->logUserAction(
            "Customer updated: {$payload->customer->getName()}",
            'customers',
            ['id' => $payload->customer->getId(), 'name' => $payload->customer->getName()],
        );

        return $payload;
    }
}
