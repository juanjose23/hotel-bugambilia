<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cobro;

use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidosConItemsQuery;
use Illuminate\Support\Collection;

/**
 * DTO que contiene los datos calculados para abrir el modal de cobro directo.
 */
final class DatosCobroDirecto
{
    /**
     * @param  int[]  $pedidoIds
     * @param  Collection<int, Pedido>  $pedidos
     */
    public function __construct(
        public readonly array $pedidoIds,
        public readonly Collection $pedidos,
        public readonly float $subtotal,
        public readonly ?int $clienteId,
        public readonly ?string $clienteNombre,
    ) {}
}

final class AbrirModalCobroDirecto
{
    public function __construct(
        private readonly ObtenerPedidosConItemsQuery $obtenerPedidos,
    ) {}

    /**
     * Prepara los datos para el modal de cobro directo de pedido(s).
     *
     * @param  int[]  $pedidoIds
     */
    public function ejecutar(array $pedidoIds): DatosCobroDirecto
    {
        $pedidos = $this->obtenerPedidos->ejecutar($pedidoIds);

        $subtotal = 0.0;
        foreach ($pedidos as $pedido) {
            $subtotal += $pedido->calcularSubtotal();
        }

        $primerPedido = $pedidos->first();
        $clienteId = $primerPedido?->cliente_id;
        $clienteNombre = null;

        if ($primerPedido?->cliente !== null) {
            $clienteNombre = $primerPedido->cliente->nombre_completo
                ?? $primerPedido->cliente->primer_nombre;
        }

        return new DatosCobroDirecto(
            pedidoIds: $pedidoIds,
            pedidos: $pedidos,
            subtotal: round($subtotal, 2),
            clienteId: $clienteId,
            clienteNombre: $clienteNombre,
        );
    }
}
