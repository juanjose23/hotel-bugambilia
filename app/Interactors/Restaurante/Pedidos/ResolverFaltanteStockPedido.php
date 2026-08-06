<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Cocina\AutorizarSustitucionIngrediente;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ResolverFaltanteStockPedido
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private AutorizarSustitucionIngrediente $autorizarSustitucion,
        private RecalcularTotalesPedido $recalcular,
    ) {}

    /**
     * @param  array<int, array{variante_original_id?: mixed, variante_sustituta_id?: mixed, cantidad_usada?: mixed}>  $sustituciones
     */
    public function ejecutar(
        Pedido $pedido,
        string $accion,
        ?int $itemId = null,
        ?int $platoId = null,
        array $sustituciones = [],
    ): Pedido {
        return DB::transaction(function () use ($pedido, $accion, $itemId, $platoId, $sustituciones): Pedido {
            if ($accion === 'cancelar_pedido') {
                $pedido->estado = EstadoPedido::CANCELADO;
                $this->repositorio->guardarPedido($pedido);

                return $pedido->refresh();
            }

            $item = $this->obtenerItemBloqueado($pedido, $itemId);

            match ($accion) {
                'sustituir_ingrediente' => $this->sustituirIngredientes($item, $sustituciones),
                'cambiar_plato' => $this->cambiarPlato($item, $platoId),
                'anular_item' => $this->anularItem($item),
                default => throw new DomainException('Acción de resolución no válida.'),
            };

            $this->recalcular->ejecutar($pedido);

            return $pedido->refresh();
        });
    }

    private function obtenerItemBloqueado(Pedido $pedido, ?int $itemId): PedidoItem
    {
        if ($itemId === null) {
            throw new DomainException('Seleccione el item que desea resolver.');
        }

        $item = $pedido->items()
            ->whereKey($itemId)
            ->where('estado', EstadoItemPedido::BLOQUEADO_STOCK->value)
            ->first();

        if (! $item instanceof PedidoItem) {
            throw new DomainException('El item seleccionado no está bloqueado por stock.');
        }

        return $item;
    }

    /**
     * @param  array<int, array{variante_original_id?: mixed, variante_sustituta_id?: mixed, cantidad_usada?: mixed}>  $sustituciones
     */
    private function sustituirIngredientes(PedidoItem $item, array $sustituciones): void
    {
        if ($sustituciones === []) {
            throw new DomainException('Agregue al menos una sustitución autorizada por el cliente.');
        }

        $detalles = collect($item->bloqueo_stock_detalle ?? [])->keyBy('variante_original_id');

        foreach ($sustituciones as $sustitucion) {
            $vOrig = $sustitucion['variante_original_id'] ?? null;
            $vSust = $sustitucion['variante_sustituta_id'] ?? null;
            $varianteOriginalId = is_numeric($vOrig) ? (int) $vOrig : 0;
            $varianteSustitutaId = is_numeric($vSust) ? (int) $vSust : 0;

            if ($varianteOriginalId <= 0 || $varianteSustitutaId <= 0) {
                throw new DomainException('Complete la variante original y la variante sustituta.');
            }

            $detalle = $detalles->get($varianteOriginalId);
            $detalleData = is_array($detalle) ? $detalle : [];
            $cantidadRequerida = is_numeric($detalleData['requerido'] ?? null) ? (float) $detalleData['requerido'] : 0.0;
            $cantidadUsada = isset($sustitucion['cantidad_usada']) && is_numeric($sustitucion['cantidad_usada'])
                ? (float) $sustitucion['cantidad_usada']
                : $cantidadRequerida;

            $this->autorizarSustitucion->ejecutar(
                item: $item,
                varianteOriginalId: $varianteOriginalId,
                varianteSustitutaId: $varianteSustitutaId,
                cantidadRequerida: $cantidadRequerida,
                cantidadUsada: $cantidadUsada,
                motivo: 'Autorizado por cliente ante faltante de stock',
            );
        }

        $this->liberarBloqueo($item);
    }

    private function cambiarPlato(PedidoItem $item, ?int $platoId): void
    {
        if ($platoId === null) {
            throw new DomainException('Seleccione el nuevo platillo autorizado por el cliente.');
        }

        $plato = $this->repositorio->obtenerPlatoConPrecios($platoId);

        if ($plato === null) {
            throw new DomainException('El platillo seleccionado no existe.');
        }

        $precio = $plato->precios->first()->precio ?? $item->precio_unitario;

        $item->plato_id = $plato->id;
        $item->precio_unitario = (float) $precio;
        $item->observaciones = trim(((string) ($item->observaciones ?? '')).' | Cambio autorizado por faltante de stock');

        $this->liberarBloqueo($item);
    }

    private function anularItem(PedidoItem $item): void
    {
        $item->estado = EstadoItemPedido::ANULADO;
        $item->bloqueo_stock_detalle = null;
        $item->bloqueado_stock_en = null;

        $this->repositorio->guardarItem($item);
    }

    private function liberarBloqueo(PedidoItem $item): void
    {
        $item->estado = EstadoItemPedido::PENDIENTE;
        $item->bloqueo_stock_detalle = null;
        $item->bloqueado_stock_en = null;

        $this->repositorio->guardarItem($item);
    }
}
