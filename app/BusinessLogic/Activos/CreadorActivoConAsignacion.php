<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Persistencia\Activos\ActivoAsignacionRepositorioInterface;
use App\Repository\Persistencia\Activos\ActivoRepositorioInterface;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorioInterface;
use App\Repository\Persistencia\Inventario\StockRepositorioInterface;
use App\Repository\Queries\Catalogos\ObtenerUbicacionAlmacen;

class CreadorActivoConAsignacion
{
    public function __construct(
        private readonly ActivoRepositorioInterface $activoRepositorio,
        private readonly ActivoAsignacionRepositorioInterface $asignacionRepositorio,
        private readonly StockRepositorioInterface $stockRepositorio,
        private readonly MovimientoStockRepositorioInterface $movimientoStockRepositorio,
        private readonly ObtenerUbicacionAlmacen $obtenerAlmacen,
    ) {}

    public function execute(
        string $codigoInventario,
        ?int $registroId,
        ?int $recepcionItemId,
        int $productoId,
        ?int $productoVarianteId,
        string $nombreDescriptivo,
        ?string $numeroSerie,
        string $fechaAdquisicion,
        ?float $costoAdquisicion,
        ?int $monedaId,
        ?int $proveedorId,
        int $userId,
        ?string $asignacionType = null,
        ?int $asignableId = null,
        ?string $asignacionMotivo = null
    ): Activo {

        $activo = $this->activoRepositorio->crear([
            'codigo_inventario' => $codigoInventario,
            'individualizacion_id' => $registroId,
            'recepcion_item_id' => $recepcionItemId,
            'producto_id' => $productoId,
            'producto_variante_id' => $productoVarianteId,
            'nombre_descriptivo' => $nombreDescriptivo,
            'numero_serie' => $numeroSerie ?: null,
            'fecha_adquisicion' => $fechaAdquisicion,
            'costo_adquisicion' => $costoAdquisicion,
            'moneda_id' => $monedaId,
            'proveedor_id' => $proveedorId,
            'vida_util_meses' => 60,
            'estado' => EstadoActivo::Activo,
        ]);

        $destinoType = $asignacionType;
        $destinoId = $asignableId;

        if (empty($destinoType) || empty($destinoId)) {
            $bodega = $this->obtenerAlmacen->ejecutar();

            if (! $bodega) {
                throw new \RuntimeException('No existe ninguna ubicación activa en el sistema para la asignación inicial.');
            }
            $destinoType = Ubicacion::class;
            $destinoId = $bodega->id;
        }

        $this->asignacionRepositorio->crear([
            'activo_id' => $activo->id,
            'asignable_type' => $destinoType,
            'asignable_id' => $destinoId,
            'fecha_inicio' => $fechaAdquisicion,
            'motivo' => $asignacionMotivo ?: 'Asignación física inicial al registrar activo fijo',
            'asignado_por_id' => $userId,
            'estado' => EstadoAsignacion::Vigente,
        ]);

        if ($destinoType === Ubicacion::class) {
            $stock = $this->stockRepositorio->buscarPorProductoUbicacion(
                productoId: $productoId,
                varianteId: $productoVarianteId,
                ubicacionId: $destinoId,
                bloquear: true
            );

            if (! $stock) {
                $stock = $this->stockRepositorio->crear([
                    'producto_id' => $productoId,
                    'producto_variante_id' => $productoVarianteId,
                    'ubicacion_id' => $destinoId,
                    'cantidad' => 1.0,
                ]);
            } else {
                $stock->cantidad += 1.0;
                $this->stockRepositorio->guardar($stock);
            }
        }

        $this->movimientoStockRepositorio->registrar([
            'tipo' => 'MOV_ENTRADA',
            'producto_id' => $productoId,
            'cantidad' => 1.0,
            'ubicacion_origen_id' => null,
            'ubicacion_destino_id' => $destinoType === Ubicacion::class ? $destinoId : null,
            'documento_tipo' => 'inv_activos',
            'documento_id' => $activo->id,
            'referencia' => "Registro de activo fijo: {$codigoInventario}",
            'creado_por_id' => $userId,
        ]);

        return $activo;
    }
}
