<?php

declare(strict_types=1);

namespace App\Interactors\Shared;

use App\BusinessLogic\Shared\Inventario\ConsumidorStock;

class RegistrarConsumoStock
{
    public function __construct(
        private readonly ConsumidorStock $consumidorStock,
    ) {}

    public function execute(
        int $stockId,
        float $cantidad,
        string $motivo = 'consumo',
        ?int $creadoPorId = null,
        ?string $referencia = null,
    ): void {
        $this->consumidorStock->consumir(
            stockId: $stockId,
            cantidad: $cantidad,
            motivo: $motivo,
            creadoPorId: $creadoPorId,
            referencia: $referencia,
        );
    }
}
