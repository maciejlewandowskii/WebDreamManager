<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\DeleteCustomer\Stage;

use App\Domain\Customer\Application\Pipeline\DeleteCustomer\DeleteCustomerCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.customer.delete', attributes: ['priority' => -200])]
final readonly class LogDeleteCustomerStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof DeleteCustomerCommand);

        $this->logUserAction(
            "Customer deleted: {$payload->customer->getName()}",
            'customers',
            ['id' => $payload->customer->getId(), 'name' => $payload->customer->getName()],
        );

        return $payload;
    }
}
