<?php

declare(strict_types=1);

namespace App\Interactors\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Repository\Persistencia\Activos\ActivoRepositorioInterface;
use App\Repository\Queries\Activos\ObtenerMantenimientosCompletados;

class SincronizarEstadoActivo
{
    public function __construct(
        private readonly ObtenerMantenimientosCompletados $obtenerMantenimientos,
        private readonly ActivoRepositorioInterface $activoRepositorio,
    ) {}

    public function execute(): int
    {
        $actualizados = 0;

        $this->obtenerMantenimientos->ejecutar(function ($mantenimientos) use (&$actualizados): void {
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
                    $this->activoRepositorio->guardar($activo);
                    $actualizados++;
                }
            }
        });

        return $actualizados;
    }
}
