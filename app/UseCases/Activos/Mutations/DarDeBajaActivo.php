<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\TipoBaja;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Activos\ActivoBaja;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use Illuminate\Support\Facades\DB;

class DarDeBajaActivo
{
    public function execute(
        int $activoId,
        TipoBaja $motivoTipo,
        string $motivoDetalle,
        int $userId,
        ?float $valorResidual = null,
        ?int $aprobadoPorId = null,
        ?string $documentoSoporte = null
    ): void {
        $activo = Activo::findOrFail($activoId);

        if ($activo->estado === EstadoActivo::DadoDeBaja) {
            throw new \RuntimeException('Este activo ya está dado de baja.');
        }

        DB::transaction(function () use ($activo, $motivoTipo, $motivoDetalle, $userId, $valorResidual, $aprobadoPorId, $documentoSoporte) {
            // 1. Obtener la última asignación física activa para conocer la ubicación del stock
            $ultimaAsignacion = ActivoAsignacion::where('activo_id', $activo->id)
                ->whereNull('fecha_fin')
                ->first();

            $ubicacionId = null;
            if ($ultimaAsignacion && $ultimaAsignacion->asignable_type === Ubicacion::class) {
                $ubicacionId = $ultimaAsignacion->asignable_id;
            }

            if (! $ubicacionId) {
                // Ubicación de bodega fallback
                $ubicacionBodega = Ubicacion::where('tipo', 'almacen')->where('estado', 1)->first()
                    ?: Ubicacion::where('estado', 1)->first();
                $ubicacionId = $ubicacionBodega?->id;
            }

            if (! $ubicacionId) {
                throw new \RuntimeException('No existe ninguna ubicación activa en el sistema.');
            }

            // 2. Cerrar asignación física anterior
            if ($ultimaAsignacion) {
                $ultimaAsignacion->update([
                    'fecha_fin' => now()->toDateString(),
                    'estado' => EstadoAsignacion::Cerrada,
                ]);
            }

            // 3. Cambiar estado del activo a DadoDeBaja
            $activo->estado = EstadoActivo::DadoDeBaja;
            $activo->save();

            // 4. Generar folio correlativo único de baja
            $ultimoBaja = ActivoBaja::withTrashed()->latest('id')->first();
            $numero = $ultimoBaja ? $ultimoBaja->id + 1 : 1;
            $codigoBaja = sprintf(
                'BAJA-%s-%s',
                now()->format('Y'),
                str_pad((string) $numero, 4, '0', STR_PAD_LEFT)
            );

            // 5. Registrar la Baja
            $baja = ActivoBaja::create([
                'codigo' => $codigoBaja,
                'activo_id' => $activo->id,
                'fecha_baja' => now()->toDateString(),
                'motivo_tipo' => $motivoTipo,
                'motivo_detalle' => $motivoDetalle,
                'valor_residual' => $valorResidual,
                'aprobado_por_id' => $aprobadoPorId,
                'creado_por_id' => $userId,
                'documento_soporte' => $documentoSoporte,
            ]);

            // 6. Decrementar el Stock Físico en la última ubicación
            $stock = Stock::lockForUpdate()->firstOrCreate([
                'producto_id' => $activo->producto_id,
                'producto_variante_id' => $activo->producto_variante_id,
                'ubicacion_id' => $ubicacionId,
            ], [
                'cantidad' => 0.0,
            ]);

            $stock->cantidad -= 1.0;
            if ($stock->cantidad < 0.0) {
                $stock->cantidad = 0.0;
            }
            $stock->save();

            // 7. Registrar Movimiento de Salida en Bitácora
            MovimientoStock::create([
                'tipo' => 'MOV_SALIDA',
                'producto_id' => $activo->producto_id,
                'cantidad' => 1.0,
                'ubicacion_origen_id' => $ubicacionId,
                'ubicacion_destino_id' => null,
                'documento_tipo' => 'inv_activo_bajas',
                'documento_id' => $baja->id,
                'referencia' => "Baja definitiva de activo {$activo->codigo_inventario} - {$motivoTipo->label()}",
                'creado_por_id' => $userId,
            ]);
        });
    }
}
