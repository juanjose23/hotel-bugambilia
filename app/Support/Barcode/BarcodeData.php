<?php

declare(strict_types=1);

namespace App\Support\Barcode;

final readonly class BarcodeData
{
    public function __construct(
        public string $code,
        public string $base64,
    ) {}
}
