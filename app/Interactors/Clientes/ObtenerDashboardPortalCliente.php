<?php

declare(strict_types=1);

namespace App\Interactors\Clientes;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Restaurante\EstadoPedido;
use App\Presenters\Clientes\PortalPresenter;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\User;
use App\Repository\Queries\Clientes\ObtenerPedidosPortalClienteQuery;
use App\Repository\Queries\Clientes\ObtenerReservasPortalClienteQuery;

final class ObtenerDashboardPortalCliente
{
    public function __construct(
        private readonly ObtenerReservasPortalClienteQuery $obtenerReservas,
        private readonly ObtenerPedidosPortalClienteQuery $obtenerPedidos,
        private readonly PortalPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function ejecutar(User $user): array
    {
        $cliente = $user->persona?->cliente;
        $clienteId = $cliente?->id;
        $email = $user->email;

        $reservas = $this->obtenerReservas->ejecutar($clienteId, $email);

        $activas = $reservas->filter(fn (Reserva $r) => in_array($r->estado, [
            EstadoReserva::PENDIENTE,
            EstadoReserva::CONFIRMADA,
            EstadoReserva::CHECKED_IN,
        ], true))->values();

        $historial = $reservas->filter(fn (Reserva $r) => in_array($r->estado, [
            EstadoReserva::CHECKED_OUT,
            EstadoReserva::CANCELADA,
            EstadoReserva::NO_SHOW,
        ], true))->values();

        $proximaEstancia = $activas->sortBy('fecha_check_in')->first();

        $pedidos = $this->obtenerPedidos->ejecutar($clienteId);

        $pedidosActivos = $pedidos->filter(fn (Pedido $p) => in_array($p->estado, [
            EstadoPedido::ABIERTO,
            EstadoPedido::EN_PREPARACION,
            EstadoPedido::LISTO,
            EstadoPedido::SERVIDO,
        ], true))->values();

        $pedidosHistorial = $pedidos->filter(fn (Pedido $p) => in_array($p->estado, [
            EstadoPedido::PAGADO,
            EstadoPedido::CARGADO_A_HABITACION,
            EstadoPedido::CANCELADO,
        ], true))->values();

        return [
            'cliente' => $this->presenter->cliente($user, $cliente),
            'estancia_activa' => $proximaEstancia !== null ? $this->presenter->reservaResumen($proximaEstancia) : null,
            'reservas_activas' => $activas->map(fn (Reserva $r) => $this->presenter->reservaResumen($r))->all(),
            'historial_reservas' => $historial->map(fn (Reserva $r) => $this->presenter->reservaResumen($r))->all(),
            'pedidos_activos' => $pedidosActivos->map(fn (Pedido $p) => $this->presenter->pedidoResumen($p))->all(),
            'historial_pedidos' => $pedidosHistorial->map(fn (Pedido $p) => $this->presenter->pedidoResumen($p))->all(),
            'estadisticas' => [
                'total_reservas' => $reservas->count(),
                'activas' => $activas->count(),
                'completadas' => $historial->where('estado', EstadoReserva::CHECKED_OUT)->count(),
                'total_pedidos' => $pedidos->count(),
                'pedidos_activos' => $pedidosActivos->count(),
                'tiene_reservas' => $reservas->isNotEmpty(),
                'tiene_pedidos' => $pedidos->isNotEmpty(),
            ],
        ];
    }
}
