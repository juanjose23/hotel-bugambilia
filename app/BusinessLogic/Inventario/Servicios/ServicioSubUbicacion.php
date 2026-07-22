<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Servicios;

use App\BusinessLogic\Inventario\Validacion\ValidacionLotes;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;

class ServicioSubUbicacion
{
    public function __construct(
        private readonly MovimientoStock $modeloMovimiento,
        private readonly ValidacionLotes $validacion,
    ) {}

    public function ejecutarAsignacion(Lote $lote, int $ubicacionDetalleId): void
    {
        $this->validacion->validarCambioSubUbicacion($lote, $ubicacionDetalleId);

        $lote->ubicacion_detalle_id = abs($ubicacionDetalleId);
        $lote->save();

        $this->modeloMovimiento->create([
            'tipo' => 'MOV_AJUSTE',
            'lote_id' => $lote->id,
            'producto_id' => $lote->producto_id,
            'cantidad' => 0,
            'ubicacion_destino_id' => $ubicacionDetalleId,
            'documento_tipo' => 'asignacion_sub_ubicacion',
            'referencia' => sprintf(
                'Asignación de sub-ubicación %d al lote %s',
                $ubicacionDetalleId,
                $lote->codigo_lote,
            ),
        ]);
    }
}
