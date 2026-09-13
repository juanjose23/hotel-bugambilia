<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Presenters\Landing\RestaurantePresenter;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;

final class ObtenerRestauranteLanding
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
        private readonly RestaurantePresenter $presenter,
    ) {}

    /**
     * @return array{
     *     restaurante: array<string, mixed>|null,
     *     ambientes: array<int, array<string, mixed>>,
     *     mesas: array<int, array<string, mixed>>,
     *     menu: array<int, array<string, mixed>>,
     *     departamentos_delivery: list<array{codigo: string, nombre: string, activo: bool, costo_envio: float, municipios: list<string>}>
     * }
     */
    public function ejecutar(): array
    {
        return $this->presenter->restaurante($this->repositorio->obtenerRestauranteParaLanding());
    }
}
