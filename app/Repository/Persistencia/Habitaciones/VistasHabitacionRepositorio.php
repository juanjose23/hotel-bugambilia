<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Habitaciones;

use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Queries\Habitaciones\ObtenerVistasHabitacionQuery;
use Illuminate\Validation\ValidationException;

final readonly class VistasHabitacionRepositorio
{
    public function __construct(private ObtenerVistasHabitacionQuery $vistas) {}

    /** @param array<int, mixed> $ids */
    public function sincronizar(Habitacion $habitacion, array $ids): void
    {
        $permitidas = $this->vistas->opciones();
        $filas = [];
        foreach ($ids as $orden => $id) {
            if ((! is_int($id) && ! (is_string($id) && ctype_digit($id))) || ! isset($permitidas[(int) $id])) {
                throw ValidationException::withMessages(['vista_ids' => 'Seleccione vistas del catalogo de habitaciones.']);
            }
            $filas[(int) $id] = ['orden' => $orden];
        }
        Habitacion::query()->whereKey($habitacion->id)->lockForUpdate()->firstOrFail();
        $habitacion->vistas()->sync($filas);
        $habitacion->unsetRelation('vistas');
    }
}
