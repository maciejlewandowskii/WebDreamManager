<?php

declare(strict_types=1);

namespace App\Domain\Customer\Application\Pipeline\CreateCustomer\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.customer.create', attributes: ['priority' => 100])]
final class PersistCustomerStage extends AbstractPersistStage {}
