<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\EstadoIndividualizacion;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Activos\PrefijoCodigo;
use App\Models\Activos\RegistroIndividualizacion;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use Illuminate\Support\Facades\DB;

class IndividualizarActivos
{
    /**
     * @param  array<int, array{numero_serie: string|null, nombre_descriptivo: string|null, notas: string|null}>  $items
     */
    public function execute(int $registroId, array $items, int $userId): void
    {
        $registro = RegistroIndividualizacion::findOrFail($registroId);

        if ($registro->estado === EstadoIndividualizacion::Completado) {
            throw new \RuntimeException('Este registro de individualización ya ha sido completado.');
        }

        $cantidadAIndividualizar = count($items);
        if ($registro->cantidad_registrada + $cantidadAIndividualizar > $registro->cantidad_total) {
            throw new \RuntimeException('La cantidad a registrar supera el total pendiente.');
        }

        // Determinar ubicación de destino (Bodega/Almacén General)
        $ubicacion = Ubicacion::where('tipo', 'almacen')
            ->where('estado', 1)
            ->first();

        if (! $ubicacion) {
            $ubicacion = Ubicacion::where('estado', 1)->first();
        }

        if (! $ubicacion) {
            throw new \RuntimeException('No existe ninguna ubicación activa en el sistema.');
        }

        // Determinar prefijo de código de inventario
        $prefijoStr = 'ACT';
        $nombreProducto = strtolower($registro->producto->nombre);
        if (str_contains($nombreProducto, 'tv') || str_contains($nombreProducto, 'tele')) {
            $prefijoStr = 'TV';
        } elseif (str_contains($nombreProducto, 'aire') || str_contains($nombreProducto, 'ac') || str_contains($nombreProducto, 'clima')) {
            $prefijoStr = 'AC';
        } elseif (str_contains($nombreProducto, 'cama') || str_contains($nombreProducto, 'colch')) {
            $prefijoStr = 'CAM';
        }

        DB::transaction(function () use ($registro, $items, $userId, $ubicacion, $prefijoStr) {
            // Cierre concurrente de prefijo
            $prefijoModel = PrefijoCodigo::lockForUpdate()->firstOrCreate(
                ['prefijo' => $prefijoStr],
                ['ultimo_numero' => 0]
            );

            // Obtener datos del Item de Recepción si existen
            $recepcionItem = $registro->recepcionItem;
            $recepcion = $recepcionItem?->recepcion;
            $costoAdq = $recepcionItem?->ordenItem?->precio_unitario;
            $monedaId = $recepcion?->ordenCompra?->moneda_id;
            $proveedorId = $recepcion?->ordenCompra?->proveedor_id;

            foreach ($items as $itemData) {
                // Incrementar el correlativo
                $prefijoModel->ultimo_numero++;
                $prefijoModel->save();

                $codigoInventario = sprintf(
                    '%s-%s-%s',
                    $prefijoModel->prefijo,
                    now()->format('Y'),
                    str_pad((string) $prefijoModel->ultimo_numero, 4, '0', STR_PAD_LEFT)
                );

                // 1. Crear el Activo
                $activo = Activo::create([
                    'codigo_inventario' => $codigoInventario,
                    'individualizacion_id' => $registro->id,
                    'recepcion_item_id' => $recepcionItem?->id,
                    'producto_id' => $registro->producto_id,
                    'producto_variante_id' => $registro->producto_variante_id,
                    'nombre_descriptivo' => $itemData['nombre_descriptivo'] ?: ($registro->producto->nombre.' - '.$codigoInventario),
                    'numero_serie' => $itemData['numero_serie'] ?: null,
                    'fecha_adquisicion' => now()->toDateString(),
                    'costo_adquisicion' => $costoAdq,
                    'moneda_id' => $monedaId,
                    'proveedor_id' => $proveedorId,
                    'vida_util_meses' => 60, // 5 años por defecto
                    'estado' => EstadoActivo::Activo,
                    'notas' => $itemData['notas'] ?: null,
                ]);

                // 2. Crear Asignación física inicial
                ActivoAsignacion::create([
                    'activo_id' => $activo->id,
                    'asignable_type' => Ubicacion::class,
                    'asignable_id' => $ubicacion->id,
                    'fecha_inicio' => now()->toDateString(),
                    'motivo' => 'Asignación física inicial tras individualización en bodega',
                    'asignado_por_id' => $userId,
                    'estado' => EstadoAsignacion::Vigente,
                ]);

                // 3. Incrementar Stock Físico General en Bodega
                $stock = Stock::lockForUpdate()->firstOrCreate([
                    'producto_id' => $registro->producto_id,
                    'producto_variante_id' => $registro->producto_variante_id,
                    'ubicacion_id' => $ubicacion->id,
                ], [
                    'cantidad' => 0.0,
                ]);

                $stock->cantidad += 1.0;
                $stock->save();

                // 4. Registrar en Bitácora de Movimiento de Inventario
                MovimientoStock::create([
                    'tipo' => 'MOV_ENTRADA',
                    'producto_id' => $registro->producto_id,
                    'cantidad' => 1.0,
                    'ubicacion_origen_id' => null,
                    'ubicacion_destino_id' => $ubicacion->id,
                    'documento_tipo' => 'inv_activos',
                    'documento_id' => $activo->id,
                    'referencia' => "Individualización de activo: {$codigoInventario}",
                    'creado_por_id' => $userId,
                ]);
            }

            // 5. Actualizar estado del registro de individualización
            $registro->cantidad_registrada += count($items);
            if ($registro->cantidad_registrada >= $registro->cantidad_total) {
                $registro->estado = EstadoIndividualizacion::Completado;
                $registro->fecha_completado = now();
                $registro->registrado_por_id = $userId;
            } else {
                $registro->estado = EstadoIndividualizacion::EnProceso;
            }
            $registro->save();
        });
    }
}
