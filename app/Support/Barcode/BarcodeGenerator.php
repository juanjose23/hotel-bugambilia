<?php

declare(strict_types=1);

namespace App\Support\Barcode;

use Picqer\Barcode\BarcodeGeneratorPNG;

final class BarcodeGenerator
{
    /**
     * @param  array<int, int>|null  $color
     */
    public function png(
        string $code,
        BarcodeType $type = BarcodeType::Code128,
        int $height = 70,
        int $widthFactor = 2,
        ?array $color = null,
    ): string {
        $generator = new BarcodeGeneratorPNG;

        return $generator->getBarcode(
            $code,
            $type->value,
            widthFactor: $widthFactor,
            height: $height,
            foregroundColor: $color ?? BarcodeColor::HOTEL,
        );
    }

    /**
     * @param  array<int, int>|null  $color
     */
    public function data(
        string $code,
        BarcodeType $type = BarcodeType::Code128,
        int $height = 70,
        int $widthFactor = 2,
        ?array $color = null,
    ): BarcodeData {
        return new BarcodeData(
            code: $code,
            base64: $this->base64(
                code: $code,
                type: $type,
                height: $height,
                widthFactor: $widthFactor,
                color: $color,
            ),
        );
    }

    /**
     * @param  array<int, int>|null  $color
     */
    public function base64(
        string $code,
        BarcodeType $type = BarcodeType::Code128,
        int $height = 70,
        int $widthFactor = 2,
        ?array $color = null,
    ): string {
        return 'data:image/png;base64,'.base64_encode(
            $this->png(
                code: $code,
                type: $type,
                height: $height,
                widthFactor: $widthFactor,
                color: $color,
            ),
        );
    }
}
