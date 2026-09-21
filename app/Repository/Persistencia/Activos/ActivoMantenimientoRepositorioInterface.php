<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\User;
use Illuminate\Support\Collection;

interface ActivoMantenimientoRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): ActivoMantenimiento;

    public function guardar(ActivoMantenimiento $mantenimiento): void;

    public function buscarPorId(int $id): ?ActivoMantenimiento;

    /** @param array<int, int|string> $estados */
    public function buscarAbiertosPorActivoPlan(int $activoId, int $planId, array $estados): bool;

    /** @return Collection<int, ActivoMantenimiento> */
    public function obtenerProgramadosPorFecha(string $fecha): Collection;

    /** @return Collection<int, ActivoMantenimiento> */
    public function obtenerProgramadosAtrasados(string $fechaLimite): Collection;

    /** @return Collection<int, ActivoMantenimiento> */
    public function obtenerEnProcesoAtrasados(string $fechaLimite): Collection;

    public function yaFueNotificado(int $mantenimientoId, string $tipo): bool;

    public function registrarNotificacion(int $mantenimientoId, string $tipo, int $enviadoAId): void;

    /** @return Collection<int, User> */
    public function obtenerTodosUsuarios(): Collection;
}
