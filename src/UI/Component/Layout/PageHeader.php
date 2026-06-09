<?php

declare(strict_types=1);

namespace App\UI\Component\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PageHeader
{
    public string $title = '';

    public string $subtitle = '';

    public bool $showBack = false;

    public string $backUrl = '';
}
