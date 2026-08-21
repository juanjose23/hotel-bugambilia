<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Lavanderia;

use App\Enums\Inventario\EstadoLote;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\Lote;
use Illuminate\Database\Eloquent\Builder;

final class ObtenerOpcionesBlancosLavanderia
{
    public function __construct(
        private ObtenerStockDisponiblePorOrigen $stockPorOrigen,
    ) {}

    /**
     * @return array<int, string>
     */
    public function execute(?int $categoriaId = null, ?string $tipoOrigen = null, ?int $origenId = null): array
    {
        if ($tipoOrigen !== null && $origenId !== null && $origenId > 0) {
            $itemsStock = $this->stockPorOrigen->execute($tipoOrigen, $origenId, $categoriaId);
            $opciones = [];

            foreach ($itemsStock as $item) {
                $nombre = trim($item['nombre_producto'].' '.($item['nombre_variante'] ? "({$item['nombre_variante']})" : ''));
                $nombre .= " · Lote: {$item['codigo_lote']} · Disp: {$item['cantidad_disponible']}";
                $opciones[$item['producto_variante_id']] = $nombre;
            }

            return $opciones;
        }

        $query = $this->buildQuery($categoriaId);

        $opciones = $query
            ->orderBy('producto_id')
            ->orderBy('nombre_variante')
            ->get()
            ->mapWithKeys(function (ProductoVariante $variante): array {
                $producto = $variante->producto;
                $nombreProducto = $producto !== null ? (string) $producto->nombre : 'Producto';
                $nombre = trim($nombreProducto.' '.($variante->nombre_variante ? "({$variante->nombre_variante})" : ''));

                return [(int) $variante->id => $nombre];
            })
            ->toArray();

        /** @var array<int, string> $opciones */
        return $opciones;
    }

    /**
     * Retorna una lista de items formateados para precargar en el repeater de Filament.
     *
     * @return list<array{producto_variante_id: int, lote_id: int|null, cantidad: null, max_qty?: float, notas: null}>
     */
    public function obtenerVariantesParaPrecarga(?int $categoriaId = null, ?string $tipoOrigen = null, ?int $origenId = null): array
    {
        if ($tipoOrigen !== null && $origenId !== null && $origenId > 0) {
            $itemsStock = $this->stockPorOrigen->execute($tipoOrigen, $origenId, $categoriaId);

            return array_map(fn (array $item): array => [
                'producto_variante_id' => $item['producto_variante_id'],
                'lote_id' => $item['lote_id'],
                'cantidad' => null,
                'max_qty' => $item['cantidad_disponible'],
                'notas' => null,
            ], $itemsStock);
        }

        $query = $this->buildQuery($categoriaId);

        /** @var list<array{producto_variante_id: int, lote_id: int|null, cantidad: null, notas: null}> $items */
        $items = $query
            ->orderBy('producto_id')
            ->orderBy('nombre_variante')
            ->get()
            ->map(function (ProductoVariante $variante): array {
                $loteId = Lote::query()
                    ->where('producto_variante_id', $variante->id)
                    ->where('cantidad_disponible', '>', 0)
                    ->orderByDesc('id')
                    ->value('id');

                if ($loteId === null) {
                    $loteId = Lote::query()
                        ->where('producto_variante_id', $variante->id)
                        ->orderByDesc('id')
                        ->value('id');
                }

                return [
                    'producto_variante_id' => (int) $variante->id,
                    'lote_id' => is_numeric($loteId) ? (int) $loteId : null,
                    'cantidad' => null,
                    'notas' => null,
                ];
            })
            ->values()
            ->all();

        return $items;
    }

    /**
     * @return Builder<ProductoVariante>
     */
    private function buildQuery(?int $categoriaId = null): Builder
    {
        $query = ProductoVariante::query()
            ->with('producto.categoria')
            ->whereHas('lotes', function (Builder $loteQuery): void {
                $loteQuery->where('estado', EstadoLote::Disponible);
            })
            ->whereHas('producto', function (Builder $productoQuery) use ($categoriaId): void {
                $productoQuery->where('estado', EstadoGeneral::Activo);

                if ($categoriaId !== null && $categoriaId > 0) {
                    $categoriaIds = [$categoriaId];
                    $hijosIds = Catalogo::query()
                        ->where('padre_id', $categoriaId)
                        ->pluck('id')
                        ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)
                        ->all();

                    $todasCategorias = array_merge($categoriaIds, $hijosIds);
                    $productoQuery->whereIn('categoria_id', $todasCategorias);
                } else {
                    $terminosLavanderia = [
                        'blanco',
                        'blancos',
                        'lavander',
                        'sábana',
                        'sabana',
                        'toalla',
                        'cortina',
                        'mantel',
                        'manteler',
                        'funda',
                        'colcha',
                        'edredón',
                        'edredon',
                        'almohada',
                        'bata',
                    ];

                    $productoQuery->where(function (Builder $pq) use ($terminosLavanderia): void {
                        foreach ($terminosLavanderia as $termino) {
                            $pq->orWhereRaw('LOWER(nombre) LIKE ?', ['%'.mb_strtolower($termino).'%'])
                                ->orWhereRaw('LOWER(COALESCE(descripcion, \'\')) LIKE ?', ['%'.mb_strtolower($termino).'%']);
                        }

                        $pq->orWhereHas('categoria', function (Builder $categoriaQuery) use ($terminosLavanderia): void {
                            $categoriaQuery->where(function (Builder $cnq) use ($terminosLavanderia): void {
                                foreach ($terminosLavanderia as $termino) {
                                    $cnq->orWhereRaw('LOWER(nombre) LIKE ?', ['%'.mb_strtolower($termino).'%'])
                                        ->orWhereRaw('LOWER(COALESCE(descripcion, \'\')) LIKE ?', ['%'.mb_strtolower($termino).'%']);
                                }
                            });
                        });
                    });
                }
            });

        return $query;
    }
}
