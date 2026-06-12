<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\DeleteInvoice\Stage;

use App\Infrastructure\Pipeline\AbstractRemoveStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.delete', attributes: ['priority' => 100])]
final class RemoveInvoiceStage extends AbstractRemoveStage {}
