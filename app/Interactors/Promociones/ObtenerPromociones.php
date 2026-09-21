<?php

declare(strict_types=1);

namespace App\Interactors\Promociones;

use App\Presenters\Promociones\PromocionPresenter;
use App\Repository\Queries\Promociones\ObtenerPromocionesPublicasQuery;

final readonly class ObtenerPromociones
{
    public function __construct(
        private ObtenerPromocionesPublicasQuery $query,
        private PromocionPresenter $presenter,
    ) {}

    /**
     * @return array{promociones: array<int, array<string, mixed>>, categorias: array<int, string>}
     */
    public function ejecutar(?string $categoria = null, ?string $busqueda = null): array
    {
        $promociones = $this->query->ejecutar($categoria, $busqueda);

        return [
            'promociones' => $this->presenter->coleccion($promociones),
            'categorias' => $this->categorias(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function categorias(): array
    {
        return $this->query->obtenerCategorias();
    }
}
