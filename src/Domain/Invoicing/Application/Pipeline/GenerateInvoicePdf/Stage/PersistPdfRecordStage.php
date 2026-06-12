<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\GenerateInvoicePdf\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.generate_pdf', attributes: ['priority' => 100])]
final class PersistPdfRecordStage extends AbstractPersistStage {}
