<?php

declare(strict_types=1);

namespace App\Interactors\Habitaciones;

use App\Repository\Persistencia\Habitaciones\HabitacionRepositorioInterface;
use Illuminate\Support\Str;

class GenerarSlugHabitacion
{
    public function __construct(
        private readonly HabitacionRepositorioInterface $repositorio
    ) {}

    public function ejecutar(string $nombre, ?int $idAIgnorar = null): string
    {
        $slug = Str::slug($nombre);

        if (blank($slug)) {
            $slug = 'habitacion';
        }

        $slugBase = $slug;
        $contador = 1;

        while ($this->repositorio->existePorSlug($slug, $idAIgnorar)) {
            $slug = $slugBase.'-'.$contador;
            $contador++;
        }

        return $slug;
    }
}
