<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Lavanderia;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Inventario\EstadoLote;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;

final class ObtenerCategoriasBlancosLavanderia
{
    public function __construct(
        private ObtenerStockDisponiblePorOrigen $stockPorOrigen,
    ) {}

    /**
     * @return array<int, string>
     */
    public function execute(?string $tipoOrigen = null, ?int $origenId = null): array
    {
        if ($tipoOrigen !== null && $origenId !== null && $origenId > 0) {
            $itemsStock = $this->stockPorOrigen->execute($tipoOrigen, $origenId);
            $catIds = array_values(array_unique(array_filter(
                array_map(fn (array $item): ?int => $item['categoria_id'], $itemsStock),
                fn (?int $id): bool => $id !== null && $id > 0,
            )));

            if (empty($catIds)) {
                return [];
            }

            /** @var array<int, string> $categorias */
            $categorias = Catalogo::query()
                ->whereIn('id', $catIds)
                ->where('estado', EstadoGeneral::Activo)
                ->orderBy('nombre')
                ->pluck('nombre', 'id')
                ->toArray();

            return $categorias;
        }

        /** @var array<int, string> $categorias */
        $categorias = Catalogo::query()
            ->whereHas('catalogoTipo', function ($query): void {
                $query->where('codigo', CatalogoTipo::CATEGORIA_PRODUCTO->value);
            })
            ->where('estado', EstadoGeneral::Activo)
            ->whereHas('productoCategoria', function ($query): void {
                $query->where('estado', EstadoGeneral::Activo)
                    ->whereHas('variantes.lotes', function ($lq): void {
                        $lq->where('estado', EstadoLote::Disponible);
                    });
            })
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray();

        return $categorias;
    }
}
