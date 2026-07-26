<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Mesas\ObtenerMesaQuery;
use InvalidArgumentException;

final class CambiarEstadoMesa
{
    public function __construct(
        private readonly ObtenerMesaQuery $mesas,
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(int $mesaId, EstadoEspacio $estado): Espacio
    {
        $mesa = $this->mesas->porId($mesaId);

        if (! $mesa instanceof Espacio) {
            throw new InvalidArgumentException('La mesa seleccionada no existe.');
        }

        $mesa->estado = $estado;
        $this->repositorio->guardarMesa($mesa);

        return $mesa;
    }
}
