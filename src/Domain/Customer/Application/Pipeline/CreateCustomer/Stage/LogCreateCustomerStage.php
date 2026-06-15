<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\CreateCustomer\Stage;

use App\Domain\Customer\Application\Pipeline\CreateCustomer\CreateCustomerCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.customer.create', attributes: ['priority' => -200])]
final readonly class LogCreateCustomerStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateCustomerCommand);
        assert($payload->result !== null);

        $this->logUserAction(
            "Customer created: {$payload->result->getName()}",
            'customers',
            ['id' => $payload->result->getId(), 'name' => $payload->result->getName()],
        );

        return $payload;
    }
}
