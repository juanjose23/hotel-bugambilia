<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\EstadoIndividualizacion;
use App\Repository\Models\Activos\RegistroIndividualizacion;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Persistencia\Activos\ActivoAsignacionRepositorioInterface;
use App\Repository\Persistencia\Activos\ActivoRepositorioInterface;
use App\Repository\Persistencia\Activos\PrefijoCodigoRepositorioInterface;
use App\Repository\Persistencia\Activos\RegistroIndividualizacionRepositorioInterface;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorioInterface;
use App\Repository\Persistencia\Inventario\StockRepositorioInterface;
use Illuminate\Support\Facades\DB;

class ProcesadorIndividualizacionActivos
{
    public function __construct(
        private readonly ActivoRepositorioInterface $activoRepositorio,
        private readonly ReglasIndividualizacion $reglas,
        private readonly GeneradorPrefijo $generadorPrefijo,
        private readonly PrefijoCodigoRepositorioInterface $prefijoCodigoRepositorio,
        private readonly ActivoAsignacionRepositorioInterface $asignacionRepositorio,
        private readonly StockRepositorioInterface $stockRepositorio,
        private readonly MovimientoStockRepositorioInterface $movimientoStockRepositorio,
        private readonly RegistroIndividualizacionRepositorioInterface $registroRepositorio,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function procesar(RegistroIndividualizacion $registro, array $items, int $usuarioId, Ubicacion $ubicacion): void
    {
        $recepcionItem = $registro->recepcionItem;
        $recepcion = $recepcionItem?->recepcion;
        $costoAdq = $recepcionItem?->ordenItem?->precio_unitario;
        $monedaId = $recepcion?->ordenCompra?->moneda_id;
        $proveedorId = $recepcion?->ordenCompra?->proveedor_id;
        $productoReg = $registro->producto;

        $prefijoStr = $this->generadorPrefijo->prefijoDesdNombre($productoReg->nombre ?? 'ACT');

        DB::transaction(function () use ($registro, $items, $usuarioId, $ubicacion, $prefijoStr, $productoReg, $recepcionItem, $costoAdq, $monedaId, $proveedorId) {
            foreach ($items as $itemData) {

                $codigoInventario = $this->prefijoCodigoRepositorio->generarSiguienteCodigo($prefijoStr);

                $activo = $this->activoRepositorio->crear([
                    'codigo_inventario' => $codigoInventario,
                    'individualizacion_id' => $registro->id,
                    'recepcion_item_id' => $recepcionItem?->id,
                    'producto_id' => $registro->producto_id,
                    'producto_variante_id' => $registro->producto_variante_id,
                    'nombre_descriptivo' => $itemData['nombre_descriptivo'] ?: (($productoReg !== null ? $productoReg->nombre : 'Producto').' - '.$codigoInventario),
                    'numero_serie' => $itemData['numero_serie'] ?: null,
                    'fecha_adquisicion' => now()->toDateString(),
                    'costo_adquisicion' => $costoAdq,
                    'moneda_id' => $monedaId,
                    'proveedor_id' => $proveedorId,
                    'vida_util_meses' => 60,
                    'estado' => EstadoActivo::Activo,
                    'notes' => null,
                    'notas' => $itemData['notas'] ?: null,
                ]);

                $this->asignacionRepositorio->crear([
                    'activo_id' => $activo->id,
                    'asignable_type' => Ubicacion::class,
                    'asignable_id' => $ubicacion->id,
                    'fecha_inicio' => now()->toDateString(),
                    'motivo' => 'Asignación inicial tras individualización en bodega',
                    'asignado_por_id' => $usuarioId,
                    'estado' => EstadoAsignacion::Vigente,
                ]);

                $stock = $this->stockRepositorio->buscarPorProductoUbicacion(
                    productoId: $registro->producto_id,
                    varianteId: $registro->producto_variante_id,
                    ubicacionId: $ubicacion->id,
                    bloquear: true
                );

                if (! $stock) {
                    $stock = $this->stockRepositorio->crear([
                        'producto_id' => $registro->producto_id,
                        'producto_variante_id' => $registro->producto_variante_id,
                        'ubicacion_id' => $ubicacion->id,
                        'cantidad' => 1.0,
                    ]);
                } else {
                    $stock->cantidad += 1.0;
                    $this->stockRepositorio->guardar($stock);
                }

                $this->movimientoStockRepositorio->registrar([
                    'tipo' => 'MOV_ENTRADA',
                    'producto_id' => $registro->producto_id,
                    'cantidad' => 1.0,
                    'ubicacion_origen_id' => null,
                    'ubicacion_destino_id' => $ubicacion->id,
                    'documento_tipo' => 'inv_activos',
                    'documento_id' => $activo->id,
                    'referencia' => "Individualización de activo: {$codigoInventario}",
                    'creado_por_id' => $usuarioId,
                ]);
            }

            $registro->cantidad_registrada += count($items);
            $nuevoEstado = $this->reglas->determinarNuevoEstado($registro->cantidad_registrada, $registro->cantidad_total);
            $registro->estado = $nuevoEstado;

            if ($nuevoEstado === EstadoIndividualizacion::Completado) {
                $registro->fecha_completado = now();
                $registro->setAttribute('registrado_por_id', $usuarioId);
            }
            $this->registroRepositorio->guardar($registro);
        });
    }
}
