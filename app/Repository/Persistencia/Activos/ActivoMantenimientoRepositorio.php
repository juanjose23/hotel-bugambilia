<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Enums\Activos\EstadoMantenimiento;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\Activos\ActivoMantenimientoNotificacion;
use App\Repository\Models\User;
use Illuminate\Support\Collection;

final readonly class ActivoMantenimientoRepositorio implements ActivoMantenimientoRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): ActivoMantenimiento
    {
        return ActivoMantenimiento::create($datos);
    }

    public function guardar(ActivoMantenimiento $mantenimiento): void
    {
        $mantenimiento->save();
    }

    public function buscarPorId(int $id): ?ActivoMantenimiento
    {
        return ActivoMantenimiento::find($id);
    }

    /** @param array<int, int|string> $estados */
    public function buscarAbiertosPorActivoPlan(int $activoId, int $planId, array $estados): bool
    {
        return ActivoMantenimiento::query()
            ->where('activo_id', $activoId)
            ->where('plan_id', $planId)
            ->whereIn('estado', $estados)
            ->exists();
    }

    /** @return Collection<int, ActivoMantenimiento> */
    public function obtenerProgramadosPorFecha(string $fecha): Collection
    {
        /** @var Collection<int, ActivoMantenimiento> $items */
        $items = ActivoMantenimiento::query()
            ->with(['activo', 'realizadoPor'])
            ->where('estado', EstadoMantenimiento::Programado)
            ->whereDate('fecha_programada', $fecha)
            ->get();

        return $items;
    }

    /** @return Collection<int, ActivoMantenimiento> */
    public function obtenerProgramadosAtrasados(string $fechaLimite): Collection
    {
        /** @var Collection<int, ActivoMantenimiento> $items */
        $items = ActivoMantenimiento::query()
            ->with(['activo', 'realizadoPor'])
            ->where('estado', EstadoMantenimiento::Programado)
            ->whereDate('fecha_programada', '<=', $fechaLimite)
            ->get();

        return $items;
    }

    /** @return Collection<int, ActivoMantenimiento> */
    public function obtenerEnProcesoAtrasados(string $fechaLimite): Collection
    {
        /** @var Collection<int, ActivoMantenimiento> $items */
        $items = ActivoMantenimiento::query()
            ->with(['activo', 'realizadoPor'])
            ->where('estado', EstadoMantenimiento::EnProceso)
            ->whereDate('fecha_programada', '<=', $fechaLimite)
            ->get();

        return $items;
    }

    public function yaFueNotificado(int $mantenimientoId, string $tipo): bool
    {
        return ActivoMantenimientoNotificacion::where('mantenimiento_id', $mantenimientoId)
            ->where('tipo', $tipo)
            ->exists();
    }

    public function registrarNotificacion(int $mantenimientoId, string $tipo, int $enviadoAId): void
    {
        ActivoMantenimientoNotificacion::create([
            'mantenimiento_id' => $mantenimientoId,
            'tipo' => $tipo,
            'canal' => 'database',
            'enviado_a' => $enviadoAId,
            'metadata' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /** @return Collection<int, User> */
    public function obtenerTodosUsuarios(): Collection
    {
        /** @var Collection<int, User> $users */
        $users = User::all();

        return $users;
    }
}
