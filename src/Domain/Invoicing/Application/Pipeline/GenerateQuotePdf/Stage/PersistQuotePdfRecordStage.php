<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\GenerateQuotePdf\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.generate_pdf', attributes: ['priority' => 100])]
final class PersistQuotePdfRecordStage extends AbstractPersistStage {}
