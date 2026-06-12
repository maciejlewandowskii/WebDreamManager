<?php

declare(strict_types=1);

namespace App\Domain\Customer\Entity;

enum PdfColorMode: string
{
    case Light = 'light';
    case Dark = 'dark';

    public function label(): string
    {
        return match ($this) {
            self::Light => 'Light',
            self::Dark  => 'Dark',
        };
    }
}
