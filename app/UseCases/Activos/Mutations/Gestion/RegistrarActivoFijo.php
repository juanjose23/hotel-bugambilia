<?php

// app/UseCases/Activos/Mutations/RegistrarActivoFijo.php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations\Gestion;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\EstadoIndividualizacion;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Activos\PrefijoCodigo;
use App\Models\Activos\RegistroIndividualizacion;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\RecepcionItem;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use Illuminate\Support\Facades\DB;

class RegistrarActivoFijo
{
    /**
     * Registra un activo fijo ya sea de forma manual o asociado a un ítem de recepción de compra.
     */
    public function execute(
        ?int $recepcionItemId,
        int $productoId,
        ?int $productoVarianteId,
        string $nombreDescriptivo,
        ?string $numeroSerie,
        ?float $costoAdquisicion,
        ?int $monedaId,
        ?int $proveedorId,
        string $fechaAdquisicion,
        int $userId,
        ?string $asignacionType = null,
        ?int $asignableId = null,
        ?string $asignacionMotivo = null
    ): Activo {
        return DB::transaction(function () use (
            $recepcionItemId,
            $productoId,
            $productoVarianteId,
            $nombreDescriptivo,
            $numeroSerie,
            $costoAdquisicion,
            $monedaId,
            $proveedorId,
            $fechaAdquisicion,
            $userId,
            $asignacionType,
            $asignableId,
            $asignacionMotivo
        ) {
            // 1. Determinar el prefijo de código de inventario del producto
            $producto = Producto::findOrFail($productoId);
            $prefijoStr = 'ACT';
            $nombreProducto = strtolower($producto->nombre);

            if (str_contains($nombreProducto, 'tv') || str_contains($nombreProducto, 'tele')) {
                $prefijoStr = 'TV';
            } elseif (str_contains($nombreProducto, 'aire') || str_contains($nombreProducto, 'ac') || str_contains($nombreProducto, 'clima')) {
                $prefijoStr = 'AC';
            } elseif (str_contains($nombreProducto, 'cama') || str_contains($nombreProducto, 'colch')) {
                $prefijoStr = 'CAM';
            }

            // Incrementar correlativo bajo bloqueo pesimista
            $prefijoModel = PrefijoCodigo::lockForUpdate()->firstOrCreate(
                ['prefijo' => $prefijoStr],
                ['ultimo_numero' => 0]
            );
            $prefijoModel->ultimo_numero++;
            $prefijoModel->save();

            $codigoInventario = sprintf(
                '%s-%s-%s',
                $prefijoModel->prefijo,
                now()->format('Y'),
                str_pad((string) $prefijoModel->ultimo_numero, 4, '0', STR_PAD_LEFT)
            );

            // 2. Gestionar la asociación a la compra
            $registroId = null;
            if ($recepcionItemId) {
                $recepcionItem = RecepcionItem::with('recepcion.ordenCompra')->findOrFail($recepcionItemId);
                $recepcion = $recepcionItem->recepcion;
                $oc = $recepcion?->ordenCompra;

                // Si viene de compra y los datos financieros están vacíos, se extraen de la misma
                $costoAdquisicion ??= $recepcionItem->ordenItem?->precio_unitario ? (float) $recepcionItem->ordenItem->precio_unitario : null;
                $monedaId ??= $oc?->moneda_id;
                $proveedorId ??= $oc?->proveedor_id;
                $productoVarianteId ??= $recepcionItem->producto_variante_id;

                // Crear/actualizar puente de RegistroIndividualizacion
                $registro = RegistroIndividualizacion::lockForUpdate()->firstOrCreate(
                    ['recepcion_item_id' => $recepcionItemId],
                    [
                        'producto_id' => $productoId,
                        'producto_variante_id' => $productoVarianteId,
                        'cantidad_total' => (int) $recepcionItem->cantidad_recibida,
                        'cantidad_registrada' => 0,
                        'estado' => EstadoIndividualizacion::Pendiente,
                        'registrado_por_id' => $userId,
                    ]
                );

                $registro->cantidad_registrada++;
                if ($registro->cantidad_registrada >= $registro->cantidad_total) {
                    $registro->estado = EstadoIndividualizacion::Completado;
                    $registro->fecha_completado = now();
                } else {
                    $registro->estado = EstadoIndividualizacion::EnProceso;
                }
                $registro->save();
                $registroId = $registro->id;
            }

            // 3. Crear el Registro de Activo
            $activo = Activo::create([
                'codigo_inventario' => $codigoInventario,
                'individualizacion_id' => $registroId,
                'recepcion_item_id' => $recepcionItemId,
                'producto_id' => $productoId,
                'producto_variante_id' => $productoVarianteId,
                'nombre_descriptivo' => $nombreDescriptivo ?: ($producto->nombre.' - '.$codigoInventario),
                'numero_serie' => $numeroSerie ?: null,
                'fecha_adquisicion' => $fechaAdquisicion,
                'costo_adquisicion' => $costoAdquisicion,
                'moneda_id' => $monedaId,
                'proveedor_id' => $proveedorId,
                'vida_util_meses' => 60, // 5 años por defecto
                'estado' => EstadoActivo::Activo,
            ]);

            // 4. Determinar ubicación física de destino (Almacén General por defecto)
            $destinoType = $asignacionType;
            $destinoId = $asignableId;

            if (empty($destinoType) || empty($destinoId)) {
                $bodega = Ubicacion::where('tipo', 'almacen')->where('estado', 1)->first()
                    ?: Ubicacion::where('estado', 1)->first();

                if (! $bodega) {
                    throw new \RuntimeException('No existe ninguna ubicación activa en el sistema para la asignación inicial.');
                }
                $destinoType = Ubicacion::class;
                $destinoId = $bodega->id;
            }

            // 5. Crear la asignación física inicial
            ActivoAsignacion::create([
                'activo_id' => $activo->id,
                'asignable_type' => $destinoType,
                'asignable_id' => $destinoId,
                'fecha_inicio' => $fechaAdquisicion,
                'motivo' => $asignacionMotivo ?: 'Asignación física inicial al registrar activo fijo',
                'asignado_por_id' => $userId,
                'estado' => EstadoAsignacion::Vigente,
            ]);

            // 6. Si se asigna a una bodega/almacen físico, incrementar Stock Físico General
            if ($destinoType === Ubicacion::class) {
                $stock = Stock::lockForUpdate()->firstOrCreate([
                    'producto_id' => $productoId,
                    'producto_variante_id' => $productoVarianteId,
                    'ubicacion_id' => $destinoId,
                ], [
                    'cantidad' => 0.0,
                ]);

                $stock->cantidad += 1.0;
                $stock->save();
            }

            // 7. Registrar en Bitácora de Movimiento de Inventario
            MovimientoStock::create([
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
        });
    }
}
