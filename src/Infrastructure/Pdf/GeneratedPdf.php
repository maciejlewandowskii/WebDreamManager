<?php

declare(strict_types=1);

namespace App\Infrastructure\Pdf;

final readonly class GeneratedPdf
{
    public function __construct(
        public string $filePath,
        public string $fileName,
    ) {}
}
