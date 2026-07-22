<?php

declare(strict_types=1);

namespace App\BusinessLogic\Catalogos\Data;

final readonly class EtiquetaProductoData
{
    public function __construct(
        public string $producto,
        public string $variante,
        public string $codigo,
        public string $barcodeBase64,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'producto' => $this->producto,
            'variante' => $this->variante,
            'codigo' => $this->codigo,
            'barcode_base64' => $this->barcodeBase64,
        ];
    }
}
