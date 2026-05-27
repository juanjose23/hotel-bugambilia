<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations;

use App\Enums\Activos\EstadoMantenimiento;
use App\Models\Activos\ActivoMantenimiento;
use App\Services\Activos\NotificadorActivos;
use Illuminate\Support\Collection;

class DetectarMantenimientosAtrasados
{
    public function __construct(private readonly NotificadorActivos $notificador) {}

    public function execute(): int
    {
        $notificados = 0;

        $mantenimientos = ActivoMantenimiento::query()
            ->with('activo')
            ->get();

        $limiteProgramado = now()->subDays(7)->startOfDay();
        $limiteEnProceso = now()->subDays(15)->startOfDay();

        /** @var Collection<int, ActivoMantenimiento> $mantenimientos */
        foreach ($mantenimientos as $mantenimiento) {
            /** @var EstadoMantenimiento|null $estado */
            $estado = $mantenimiento->estado;

            $rawEstado = $mantenimiento->getRawOriginal('estado');
            if ($rawEstado === null) {
                continue;
            }

            // obtener enum casteado
            $estado = $mantenimiento->estado;

            if ($estado !== EstadoMantenimiento::Programado && $estado !== EstadoMantenimiento::EnProceso) {
                continue;
            }

            $fechaInicio = $mantenimiento->fecha_programada;

            if ($estado === EstadoMantenimiento::Programado && $fechaInicio->lte($limiteProgramado)) {
                $this->notificador->mantenimientoAtrasado($mantenimiento, (int) now()->diffInDays($fechaInicio));
                $notificados++;

                continue;
            }

            if ($estado === EstadoMantenimiento::EnProceso && $fechaInicio->lte($limiteEnProceso)) {
                $this->notificador->mantenimientoProlongado($mantenimiento, (int) now()->diffInDays($fechaInicio));
                $notificados++;
            }
        }

        return $notificados;
    }
}
