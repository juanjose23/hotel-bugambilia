<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Presenters\Restaurante\RestaurantePresenter;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;

final readonly class ObtenerRestaurante
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private RestaurantePresenter $presenter,
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
    public function execute(): array
    {
        return $this->ejecutar();
    }

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
        return $this->presenter->restaurante($this->repositorio->obtenerRestaurantePublico());
    }
}
