<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Habitaciones\Habitacion;

interface HabitacionRepositorioInterface
{
    public function existePorSlug(string $slug, ?int $idAIgnorar = null): bool;

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Habitacion;

    public function buscarPorId(int $id): ?Habitacion;

    public function buscarPorIdConLock(int $id): Habitacion;

    public function buscarPorRecursoReservableIdConLock(int $recursoReservableId): ?Habitacion;

    public function actualizarEstado(Habitacion $habitacion, EstadoEspacio $estado): void;
}
