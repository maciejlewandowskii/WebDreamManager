<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\UpdateCustomer\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.customer.update', attributes: ['priority' => 100])]
final class PersistUpdatedCustomerStage extends AbstractPersistStage {}
