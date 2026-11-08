<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Interactors\Restaurante\Pedidos\DescontarStockProductoPedido;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerItemPedidoQuery;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ConfirmarRetiroComanda
{
    public function __construct(
        private ObtenerItemPedidoQuery $items,
        private ExistenciasCocinaRepositorio $existencias,
        private ConsumirIngredientesPedido $ingredientes,
        private DescontarStockProductoPedido $productos,
    ) {}

    /** @return array{lineas: list<string>, confirmacion: string, completo: bool}|null */
    public function plan(PedidoItem $item): ?array
    {
        if ($item->esProducto() && $item->producto_id === null) {
            return null;
        }

        if ($this->existencias->tieneConsumo($item->id)) {
            return null;
        }

        $item->loadMissing(['producto', 'pedido', 'plato.receta']);
        $retiros = [];
        $lineas = [];
        $completo = true;
        $necesidades = $item->esProducto()
            ? [['cantidad' => (float) $item->cantidad, 'nombre' => $item->producto->nombre ?? 'Producto', 'ubicacion_id' => null, 'variante_id' => $item->producto_variante_id]]
            : $this->ingredientes->necesidades($item);

        foreach ($necesidades as $necesidad) {
            $ubicacionId = $necesidad['ubicacion_id'];
            $nombre = $necesidad['nombre'];
            $pendiente = $necesidad['cantidad'];
            $stocks = $ubicacionId !== null
                ? $this->existencias->porVariante((int) $necesidad['variante_id'], $ubicacionId)
                : $this->existencias->obtener($this->existencias->producto((int) $item->producto_id, $item->producto_variante_id, $item->pedido?->mesa_id));

            $stocks->loadMissing(['stockable', 'variante.unidadMedida', 'variante.producto.unidadMedida', 'producto.unidadMedida']);
            foreach ($stocks as $stock) {
                $cantidad = round(min($pendiente, (float) $stock->cantidad_actual - ($retiros[$stock->id] ?? 0.0)), 4);
                if ($cantidad <= 0) {
                    continue;
                }

                $retiros[$stock->id] = ($retiros[$stock->id] ?? 0.0) + $cantidad;
                $ubicacion = $stock->stockable;
                $origen = $ubicacion instanceof Ubicacion || $ubicacion instanceof Espacio ? $ubicacion->nombre : 'Ubicación desconocida';
                $codigo = $stock->lote->codigo_lote ?? 'Sin lote';
                $vence = $stock->lote?->fecha_vencimiento?->format('d/m/Y') ?? 'Sin vencimiento';
                $unidad = $stock->variante->unidadMedida->nombre ?? $stock->variante->producto->unidadMedida->nombre ?? $stock->producto->unidadMedida->nombre ?? 'unidades';
                $lineas[] = "{$nombre} · {$origen} · {$codigo} · {$vence} · Retirar {$cantidad} {$unidad}";
                $pendiente = round($pendiente - $cantidad, 4);
                if ($pendiente <= 0.00001) {
                    break;
                }
            }

            if ($pendiente > 0.00001) {
                $lineas[] = "{$nombre} · Faltan {$pendiente}";
                $completo = false;
            }
        }

        return $necesidades === [] ? null : [
            'lineas' => $lineas,
            'confirmacion' => $this->huella($item->id, $retiros),
            'completo' => $completo,
        ];
    }

    public function ejecutar(int $itemId, string $confirmacion, ?int $usuarioId): void
    {
        DB::transaction(function () use ($itemId, $confirmacion, $usuarioId): void {
            $item = $this->items->bloquear($itemId) ?? throw new DomainException('Partida no encontrada.');
            if ($this->existencias->tieneConsumo($itemId)) {
                return;
            }
            if ($item->estado !== EstadoItemPedido::EN_PREPARACION) {
                throw new DomainException('La partida debe estar en preparación para confirmar el retiro.');
            }

            $retiros = $item->esProducto() ? $this->productos->ejecutar($item, $usuarioId) : $this->ingredientes->ejecutar($item, $usuarioId);
            if (! hash_equals($this->huella($itemId, $retiros), $confirmacion)) {
                throw new DomainException('La lista de lotes cambió. Revisa los lotes actuales antes de confirmar.');
            }
        });
    }

    /** @param array<int, float> $retiros */
    private function huella(int $itemId, array $retiros): string
    {
        ksort($retiros);
        $retiros = array_map(static fn (float $cantidad): float => round($cantidad, 4), $retiros);

        return hash('sha256', json_encode([$itemId, $retiros], JSON_THROW_ON_ERROR));
    }
}
