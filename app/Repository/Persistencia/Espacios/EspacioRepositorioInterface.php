<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Espacios;

use App\Repository\Models\Espacios\Espacio;

interface EspacioRepositorioInterface
{
    public function buscarPorId(int $id): ?Espacio;

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Espacio;
}
