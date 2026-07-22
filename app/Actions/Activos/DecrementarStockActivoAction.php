<?php

declare(strict_types=1);

namespace App\Actions\Activos;

use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoBaja;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorioInterface;
use App\Repository\Persistencia\Inventario\StockRepositorioInterface;

class DecrementarStockActivoAction
{
    public function __construct(
        private readonly StockRepositorioInterface $stockRepositorio,
        private readonly MovimientoStockRepositorioInterface $movimientoStockRepositorio,
    ) {}

    public function ejecutar(Activo $activo, int $ubicacionId, ActivoBaja $baja, int $userId, string $referencia): void
    {
        $stock = $this->stockRepositorio->buscarPorProductoUbicacion(
            productoId: $activo->producto_id,
            varianteId: $activo->producto_variante_id,
            ubicacionId: $ubicacionId,
            bloquear: true
        );

        if (! $stock) {
            $stock = $this->stockRepositorio->crear([
                'producto_id' => $activo->producto_id,
                'producto_variante_id' => $activo->producto_variante_id,
                'ubicacion_id' => $ubicacionId,
                'cantidad' => 0.0,
            ]);
        } else {

            $stock->cantidad -= 1.0;
            if ($stock->cantidad < 0.0) {
                $stock->cantidad = 0.0;
            }
            $this->stockRepositorio->guardar($stock);
        }

        $this->movimientoStockRepositorio->registrar([
            'tipo' => 'MOV_SALIDA',
            'producto_id' => $activo->producto_id,
            'cantidad' => 1.0,
            'ubicacion_origen_id' => $ubicacionId,
            'ubicacion_destino_id' => null,
            'documento_tipo' => 'inv_activo_bajas',
            'documento_id' => $baja->id,
            'referencia' => $referencia,
            'creado_por_id' => $userId,
        ]);
    }
}
