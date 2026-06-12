<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateQuote\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.create', attributes: ['priority' => 100])]
final class PersistQuoteStage extends AbstractPersistStage {}
