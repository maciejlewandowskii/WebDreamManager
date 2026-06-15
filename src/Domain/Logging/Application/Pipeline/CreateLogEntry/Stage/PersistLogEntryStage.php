<?php

declare(strict_types=1);

namespace App\Domain\Logging\Application\Pipeline\CreateLogEntry\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.logging.create', attributes: ['priority' => 0])]
final class PersistLogEntryStage extends AbstractPersistStage
{
}
