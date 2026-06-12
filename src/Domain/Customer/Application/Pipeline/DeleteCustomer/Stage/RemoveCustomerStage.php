<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\DeleteCustomer\Stage;

use App\Infrastructure\Pipeline\AbstractRemoveStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.customer.delete', attributes: ['priority' => 100])]
final class RemoveCustomerStage extends AbstractRemoveStage {}
