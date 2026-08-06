<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Shared\Stock;
use Illuminate\Support\Collection;

final class ObtenerAbastecimientoInteligenteCocina
{
    /**
     * @return array{
     *     motivo: string,
     *     fecha_necesita: string,
     *     items: list<array{producto_variante_id: int|null, cantidad: float, justificacion: string}>
     * }
     */
    public function ejecutar(): array
    {
        $sugerencias = collect();
        /** @var list<int> $ubicaciones */
        $ubicaciones = array_values(array_filter(
            $this->ubicacionesCocina()->pluck('id')->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)->all(),
            fn (int $id): bool => $id > 0
        ));

        if ($ubicaciones !== []) {
            $sugerencias = $sugerencias
                ->merge($this->sugerirPorStockIdeal($ubicaciones))
                ->merge($this->sugerirPorPedidosBloqueados());
        }

        $items = $this->agruparSugerencias($sugerencias);

        if ($items === []) {
            $items[] = [
                'producto_variante_id' => null,
                'cantidad' => 1.0,
                'justificacion' => 'Solicitud manual de abastecimiento para cocina.',
            ];
        }

        return [
            'motivo' => $this->motivo($items),
            'fecha_necesita' => now()->addDay()->toDateString(),
            'items' => $items,
        ];
    }

    /** @return Collection<int, Ubicacion> */
    private function ubicacionesCocina(): Collection
    {
        /** @var Collection<int, Ubicacion> $ubicaciones */
        $ubicaciones = Ubicacion::query()
            ->where('estado', 1)
            ->where(function ($query): void {
                $query
                    ->where('nombre', UbicacionCocina::RESTAURANTE->value)
                    ->orWhere('nombre', 'Cocina')
                    ->orWhere('nombre', 'like', '%Cocina%')
                    ->orWhere('tipo', 'cocina');
            })
            ->get(['id', 'nombre']);

        return $ubicaciones;
    }

    /**
     * @param  list<int>  $ubicacionIds
     * @return Collection<int, array{producto_variante_id: int, cantidad: float, justificacion: string}>
     */
    private function sugerirPorStockIdeal(array $ubicacionIds): Collection
    {
        /** @var Collection<int, array{producto_variante_id: int, cantidad: float, justificacion: string}> $res */
        $res = Stock::query()
            ->with(['variante.producto', 'variante.unidadMedida'])
            ->where('stockable_type', Ubicacion::class)
            ->whereIn('stockable_id', $ubicacionIds)
            ->whereColumn('cantidad_actual', '<', 'cantidad_ideal')
            ->whereNotNull('producto_variante_id')
            ->get()
            ->map(function (Stock $stock): array {
                $cantidad = max(0.0, (float) $stock->cantidad_ideal - (float) $stock->cantidad_actual);
                $nombre = $this->nombreVariante($stock->variante);

                return [
                    'producto_variante_id' => (int) $stock->producto_variante_id,
                    'cantidad' => $cantidad,
                    'justificacion' => sprintf(
                        'Reposición sugerida para %s. Stock actual: %s; ideal: %s.',
                        $nombre,
                        number_format((float) $stock->cantidad_actual, 2),
                        number_format((float) $stock->cantidad_ideal, 2),
                    ),
                ];
            })
            ->filter(fn (array $item): bool => $item['cantidad'] > 0)
            ->values();

        return $res;
    }

    /**
     * @return Collection<int, array{producto_variante_id: int, cantidad: float, justificacion: string}>
     */
    private function sugerirPorPedidosBloqueados(): Collection
    {
        /** @var Collection<int, array{producto_variante_id: int, cantidad: float, justificacion: string}> $res */
        $res = PedidoItem::query()
            ->where('estado', EstadoItemPedido::BLOQUEADO_STOCK->value)
            ->whereNotNull('bloqueo_stock_detalle')
            ->get(['id', 'bloqueo_stock_detalle'])
            ->flatMap(function (PedidoItem $item): array {
                /** @var list<array<string, mixed>> $detalles */
                $detalles = is_array($item->bloqueo_stock_detalle) ? array_values($item->bloqueo_stock_detalle) : [];
                $sugerencias = [];

                foreach ($detalles as $detalle) {
                    $varianteId = is_numeric($detalle['variante_original_id'] ?? null)
                        ? (int) $detalle['variante_original_id']
                        : 0;
                    $faltante = is_numeric($detalle['faltante'] ?? null)
                        ? (float) $detalle['faltante']
                        : 0.0;
                    $ingrediente = is_string($detalle['ingrediente'] ?? null)
                        ? $detalle['ingrediente']
                        : "Variante {$varianteId}";

                    if ($varianteId <= 0 || $faltante <= 0) {
                        continue;
                    }

                    $sugerencias[] = [
                        'producto_variante_id' => $varianteId,
                        'cantidad' => $faltante,
                        'justificacion' => "Pedido bloqueado por falta de {$ingrediente}.",
                    ];
                }

                return $sugerencias;
            })
            ->values();

        return $res;
    }

    /**
     * @param  Collection<int, array{producto_variante_id: int, cantidad: float, justificacion: string}>  $sugerencias
     * @return list<array{producto_variante_id: int|null, cantidad: float, justificacion: string}>
     */
    private function agruparSugerencias(Collection $sugerencias): array
    {
        /** @var list<array{producto_variante_id: int|null, cantidad: float, justificacion: string}> $items */
        $items = $sugerencias
            ->groupBy('producto_variante_id')
            ->map(function (Collection $grupo, int|string $varianteId): array {
                $sum = $grupo->sum('cantidad');
                $cantidad = is_numeric($sum) ? round((float) $sum, 2) : 0.0;
                $justificaciones = (string) $grupo
                    ->pluck('justificacion')
                    ->filter()
                    ->unique()
                    ->take(3)
                    ->implode(' ');

                return [
                    'producto_variante_id' => is_numeric($varianteId) ? (int) $varianteId : null,
                    'cantidad' => $cantidad,
                    'justificacion' => $justificaciones,
                ];
            })
            ->filter(fn (array $item): bool => $item['cantidad'] > 0)
            ->sortByDesc('cantidad')
            ->values()
            ->all();

        return $items;
    }

    /**
     * @param  list<array{producto_variante_id: int|null, cantidad: float, justificacion: string}>  $items
     */
    private function motivo(array $items): string
    {
        if (count($items) === 1 && $items[0]['producto_variante_id'] === null) {
            return 'Solicitud manual de abastecimiento para cocina.';
        }

        return 'Solicitud inteligente de abastecimiento por stock bajo y pedidos bloqueados en cocina.';
    }

    private function nombreVariante(?ProductoVariante $variante): string
    {
        if (! $variante instanceof ProductoVariante) {
            return 'producto sin variante';
        }

        $producto = $variante->producto?->nombre;
        $nombre = $variante->nombre_variante ?: $variante->codigo;
        $unidad = $variante->unidadMedida?->nombre;
        $suffix = is_string($unidad) && trim($unidad) !== '' ? " ({$unidad})" : '';

        return trim((string) ($producto ?: 'Producto'))." - {$nombre}{$suffix}";
    }
}
