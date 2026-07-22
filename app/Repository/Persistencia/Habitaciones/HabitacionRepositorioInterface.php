<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Habitaciones;

use App\Repository\Models\Habitaciones\Habitacion;

interface HabitacionRepositorioInterface
{
    public function existePorSlug(string $slug, ?int $idAIgnorar = null): bool;

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Habitacion;

    public function buscarPorId(int $id): ?Habitacion;
}
