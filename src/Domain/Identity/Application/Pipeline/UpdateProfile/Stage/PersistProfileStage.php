<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\UpdateProfile\Stage;

use App\Infrastructure\Pipeline\AbstractPersistStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.profile.update', attributes: ['priority' => 100])]
final class PersistProfileStage extends AbstractPersistStage {}
