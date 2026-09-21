<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Espacios;

use App\Repository\Models\Espacios\Espacio;

final class EspacioRepositorio implements EspacioRepositorioInterface
{
    public function buscarPorId(int $id): ?Espacio
    {
        return Espacio::query()->find($id);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Espacio
    {
        return Espacio::query()->create($datos);
    }
}
