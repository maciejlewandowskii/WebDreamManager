<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateInvoiceFromTimeTracking\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.from_time_tracking', attributes: ['priority' => 100])]
final class PersistInvoiceFromTimeTrackingStage extends AbstractPersistStage {}
