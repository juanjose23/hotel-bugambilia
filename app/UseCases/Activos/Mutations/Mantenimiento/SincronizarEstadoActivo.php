<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations\Mantenimiento;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Models\Activos\ActivoMantenimiento;

class SincronizarEstadoActivo
{
    public function execute(): int
    {
        $actualizados = 0;

        ActivoMantenimiento::query()
            ->with('activo')
            ->where('estado', EstadoMantenimiento::Completado->value)
            ->chunkById(200, function ($mantenimientos) use (&$actualizados): void {
                foreach ($mantenimientos as $mantenimiento) {
                    $activo = $mantenimiento->activo;
                    if (! $activo) {
                        continue;
                    }

                    $estadoActivo = $activo->estado;
                    if ($estadoActivo === EstadoActivo::EnMantenimiento) {
                        // Verificar si todavía hay mantenimientos abiertos
                        $tieneMantenimientoAbierto = $activo->mantenimientos()
                            ->whereIn('estado', [EstadoMantenimiento::Programado->value, EstadoMantenimiento::EnProceso->value])
                            ->exists();

                        if ($tieneMantenimientoAbierto) {
                            continue;
                        }

                        // Restaurar al estado activo usando la instancia del enum
                        $activo->estado = EstadoActivo::Activo;
                        $activo->save();
                        $actualizados++;
                    }
                }
            });

        return $actualizados;
    }
}
