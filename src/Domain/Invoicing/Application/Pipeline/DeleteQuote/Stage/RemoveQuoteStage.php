<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\DeleteQuote\Stage;

use App\Infrastructure\Pipeline\AbstractRemoveStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.quote.delete', attributes: ['priority' => 100])]
final class RemoveQuoteStage extends AbstractRemoveStage {}
