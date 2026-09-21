<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Servicios;

use App\BusinessLogic\Inventario\Validacion\ValidacionLotes;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Persistencia\Inventario\LoteRepositorioInterface;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorioInterface;

final readonly class ServicioSubUbicacion
{
    public function __construct(
        private LoteRepositorioInterface $loteRepositorio,
        private MovimientoStockRepositorioInterface $movimientoStockRepositorio,
        private ValidacionLotes $validacion,
    ) {}

    public function ejecutarAsignacion(Lote $lote, int $ubicacionDetalleId): void
    {
        $this->validacion->validarCambioSubUbicacion($lote, $ubicacionDetalleId);

        $lote->ubicacion_detalle_id = abs($ubicacionDetalleId);
        $this->loteRepositorio->guardar($lote);

        $this->movimientoStockRepositorio->registrar([
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
