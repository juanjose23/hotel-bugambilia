<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Servicios;

use App\BusinessLogic\Inventario\Validacion\ValidacionLotes;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;

class ServicioMermas
{
    public function __construct(
        private readonly MovimientoStock $modeloMovimiento,
        private readonly ValidacionLotes $validacion,
    ) {}

    public function ejecutarMerma(
        Lote $lote,
        float $cantidad,
        ?string $motivo = null,
        ?int $creadoPorId = null
    ): void {
        $this->validacion->validarCantidadMerma($cantidad, $lote);

        $lote->cantidad_disponible -= $cantidad;
        $lote->estado = $this->validacion->determinarEstadoPorStock($lote);
        $lote->save();

        $costoTotal = $lote->costo_unitario !== null
            ? $lote->costo_unitario * $cantidad
            : null;

        $this->modeloMovimiento->create([
            'tipo' => 'BAJA_CALIDAD',
            'lote_id' => $lote->id,
            'producto_id' => $lote->producto_id,
            'cantidad' => -$cantidad,
            'costo_unitario' => $lote->costo_unitario,
            'costo_total' => $costoTotal,
            'ubicacion_origen_id' => $lote->ubicacion_id,
            'documento_tipo' => 'merma',
            'referencia' => $motivo ?: sprintf('Merma de lote %s', $lote->codigo_lote),
            'creado_por_id' => $creadoPorId,
            'notas' => $motivo,
        ]);
    }
}
