<?php

declare(strict_types=1);

namespace App\UI\Twig;

use App\Domain\Integration\Application\IntegrationStatusService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class IntegrationExtension extends AbstractExtension
{
    public function __construct(
        private readonly IntegrationStatusService $status,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_integration_enabled', $this->status->isEnabled(...)),
        ];
    }
}
