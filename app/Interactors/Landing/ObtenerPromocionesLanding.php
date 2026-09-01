<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Presenters\Landing\PromocionPresenter;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Queries\Promociones\ObtenerPromocionesPublicasQuery;

final class ObtenerPromocionesLanding
{
    public function __construct(
        private readonly ObtenerPromocionesPublicasQuery $query,
        private readonly PromocionPresenter $presenter,
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
        $tipoIds = Promocion::activos()->whereNotNull('tipo_promocion_id')->pluck('tipo_promocion_id')->unique();

        return Catalogo::query()
            ->whereIn('id', $tipoIds)
            ->pluck('nombre')
            ->filter()
            ->map(fn (mixed $nombre): string => is_string($nombre) ? $nombre : '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
