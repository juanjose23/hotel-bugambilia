<?php

declare(strict_types=1);

namespace App\Support\Barcode;

use Picqer\Barcode\BarcodeGenerator;

enum BarcodeType: string
{
    case Code128 = BarcodeGenerator::TYPE_CODE_128;

    case Code39 = BarcodeGenerator::TYPE_CODE_39;

    case Ean13 = BarcodeGenerator::TYPE_EAN_13;

    case Ean8 = BarcodeGenerator::TYPE_EAN_8;
}
