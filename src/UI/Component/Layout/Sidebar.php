<?php

declare(strict_types=1);

namespace App\UI\Component\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Sidebar
{
    public string $currentRoute = '';
}
