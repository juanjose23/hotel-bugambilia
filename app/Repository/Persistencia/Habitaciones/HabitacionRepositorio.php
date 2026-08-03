<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Habitaciones\Habitacion;

final class HabitacionRepositorio implements HabitacionRepositorioInterface
{
    public function existePorSlug(string $slug, ?int $idAIgnorar = null): bool
    {
        $consulta = Habitacion::withTrashed()->where('slug', $slug);

        if ($idAIgnorar !== null) {
            $consulta->where('id', '!=', $idAIgnorar);
        }

        return $consulta->exists();
    }

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Habitacion
    {
        return Habitacion::create($datos);
    }

    public function buscarPorId(int $id): ?Habitacion
    {
        return Habitacion::find($id);
    }

    public function actualizarEstado(Habitacion $habitacion, EstadoEspacio $estado): void
    {
        $habitacion->update(['estado' => $estado]);
    }
}
