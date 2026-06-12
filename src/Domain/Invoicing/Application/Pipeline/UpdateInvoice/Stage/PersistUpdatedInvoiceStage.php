<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\UpdateInvoice\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.update', attributes: ['priority' => 100])]
final class PersistUpdatedInvoiceStage extends AbstractPersistStage {}
