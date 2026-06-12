<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\ChangePassword\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.password.change', attributes: ['priority' => 100])]
final class PersistPasswordChangeStage extends AbstractPersistStage {}
