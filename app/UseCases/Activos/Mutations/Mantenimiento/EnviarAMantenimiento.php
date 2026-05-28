<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations\Mantenimiento;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Activos\ActivoMantenimiento;
use App\Models\Catalogos\Ubicacion;
use Illuminate\Support\Facades\DB;

class EnviarAMantenimiento
{
    public function execute(
        int $activoId,
        TipoMantenimiento $tipo,
        string $descripcion,
        int $userId,
        ?float $costo = null,
        ?int $monedaId = null,
        ?int $proveedorId = null,
        ?string $notas = null
    ): void {
        $activo = Activo::findOrFail($activoId);

        if ($activo->estado === EstadoActivo::DadoDeBaja) {
            throw new \RuntimeException('No se puede enviar a mantenimiento un activo dado de baja.');
        }

        // Buscar ubicación de mantenimiento/taller o por defecto almacén
        $driver = DB::connection()->getDriverName();
        $likeOperator = $driver === 'sqlite' ? 'like' : 'ilike';

        $ubicacionTaller = Ubicacion::where(function ($query) use ($likeOperator) {
            $query->where('nombre', $likeOperator, '%mantenimiento%')
                ->orWhere('nombre', $likeOperator, '%taller%');
        })
            ->where('estado', 1)
            ->first();

        if (! $ubicacionTaller) {
            $ubicacionTaller = Ubicacion::where('tipo', 'almacen')
                ->where('estado', 1)
                ->first();
        }

        if (! $ubicacionTaller) {
            $ubicacionTaller = Ubicacion::where('estado', 1)->first();
        }

        if (! $ubicacionTaller) {
            throw new \RuntimeException('No existe ninguna ubicación activa en el sistema.');
        }

        DB::transaction(function () use ($activo, $tipo, $descripcion, $userId, $costo, $monedaId, $proveedorId, $notas, $ubicacionTaller) {
            // 1. Cambiar estado del activo
            $activo->estado = EstadoActivo::EnMantenimiento;
            $activo->save();

            // 2. Cerrar asignación física anterior
            ActivoAsignacion::where('activo_id', $activo->id)
                ->whereNull('fecha_fin')
                ->update([
                    'fecha_fin' => now()->toDateString(),
                    'estado' => EstadoAsignacion::Cerrada,
                ]);

            // 3. Crear asignación de taller
            ActivoAsignacion::create([
                'activo_id' => $activo->id,
                'asignable_type' => Ubicacion::class,
                'asignable_id' => $ubicacionTaller->id,
                'fecha_inicio' => now()->toDateString(),
                'motivo' => "Ingreso a taller de mantenimiento ({$tipo->label()})",
                'asignado_por_id' => $userId,
                'estado' => EstadoAsignacion::Vigente,
            ]);

            // 4. Crear registro de mantenimiento
            ActivoMantenimiento::create([
                'activo_id' => $activo->id,
                'tipo' => $tipo,
                'fecha_programada' => now()->toDateString(),
                'descripcion' => $descripcion,
                'costo' => $costo,
                'moneda_id' => $monedaId,
                'proveedor_id' => $proveedorId,
                'realizado_por_id' => $userId,
                'estado' => EstadoMantenimiento::EnProceso,
                'notas' => $notas,
            ]);
        });
    }
}
