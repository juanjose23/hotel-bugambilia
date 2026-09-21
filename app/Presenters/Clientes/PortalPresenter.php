<?php

declare(strict_types=1);

namespace App\Presenters\Clientes;

use App\BusinessLogic\Restaurante\Delivery\CalcularCostoYDetalleDelivery;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\User;

final class PortalPresenter
{
    public function __construct(
        private readonly CalcularCostoYDetalleDelivery $calcularCostoEntrega,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function cliente(User $user, ?Cliente $cliente): array
    {
        $persona = $user->persona;

        return [
            'id' => $cliente !== null ? (int) $cliente->id : (int) $user->id,
            'usuario_id' => (int) $user->id,
            'nombre' => (string) $user->name,
            'email' => (string) $user->email,
            'telefono' => $persona !== null ? $persona->telefono : ($cliente !== null ? $cliente->telefono : null),
            'identificacion' => null,
            'tipo_identificacion' => null,
            'codigo_cliente' => $cliente !== null && is_string($cliente->codigo_cliente ?? null) ? $cliente->codigo_cliente : null,
            'tipo_cliente' => $cliente !== null && $cliente->tipoCliente !== null ? (string) $cliente->tipoCliente->nombre : 'Huésped',
            'avatar' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function reservaResumen(Reserva $reserva): array
    {
        $habitacion = $reserva->habitacion;
        $espacio = $reserva->espacio;
        $imagen = $habitacion !== null && $habitacion->imagenes->first() !== null ? (string) $habitacion->imagenes->first()->url : null;
        $recursoNombre = $habitacion !== null ? (string) $habitacion->nombre : ($espacio !== null ? (string) $espacio->nombre : 'Hospedaje');
        $categoriaNombre = $habitacion !== null && $habitacion->categoria !== null ? (string) $habitacion->categoria->nombre : 'Suite';

        return [
            'id' => (int) $reserva->id,
            'codigo_reserva' => (string) $reserva->codigo_reserva,
            'estado' => $reserva->estado->value,
            'estado_label' => $reserva->estado->getLabel(),
            'tipo_reserva' => $reserva->tipo_reserva->value,
            'tipo_reserva_label' => $reserva->tipo_reserva->getLabel(),
            'fecha_check_in' => $reserva->fecha_check_in?->format('Y-m-d'),
            'fecha_check_out' => $reserva->fecha_check_out?->format('Y-m-d'),
            'hora_reserva' => $reserva->hora_reserva,
            'noches' => $reserva->noches,
            'adultos' => $reserva->adultos,
            'ninos' => $reserva->ninos,
            'total' => (float) $reserva->total,
            'total_pagado' => (float) $reserva->total_pagado,
            'saldo' => (float) $reserva->saldo,
            'moneda_simbolo' => $reserva->moneda !== null ? (string) $reserva->moneda->simbolo : '$',
            'es_habitacion' => in_array($reserva->tipo_reserva, [TipoReserva::HABITACION, TipoReserva::PAQUETE], true),
            'es_restaurante' => $reserva->tipo_reserva === TipoReserva::RESTAURANTE,
            'es_servicio' => $reserva->tipo_reserva === TipoReserva::SERVICIO,
            'recurso' => [
                'id' => $habitacion !== null ? (int) $habitacion->id : ($espacio !== null ? (int) $espacio->id : null),
                'nombre' => $recursoNombre,
                'categoria' => $categoriaNombre,
                'imagen' => $imagen,
            ],
            'puede_cancelar' => in_array($reserva->estado, [EstadoReserva::PENDIENTE, EstadoReserva::CONFIRMADA], true),
            'url_voucher' => route('reservas.voucher', [
                'reserva' => $reserva->id,
                'codigo' => $reserva->codigo_reserva,
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function pedidoResumen(Pedido $pedido): array
    {
        $items = $pedido->items->map(function (PedidoItem $item): array {
            $plato = $item->plato;

            return [
                'id' => (int) $item->id,
                'nombre' => $plato !== null ? (string) $plato->nombre : 'Platillo',
                'cantidad' => (int) $item->cantidad,
                'precio_unitario' => (float) $item->precio_unitario,
                'subtotal' => (float) $item->subtotal,
                'observaciones' => $item->observaciones,
            ];
        })->all();

        $subtotal = (float) $pedido->subtotal;
        $detalleDelivery = $this->calcularCostoEntrega->ejecutar(is_string($pedido->notas) ? $pedido->notas : null);
        $costoDelivery = $detalleDelivery['costo_envio'];
        $total = round($subtotal + $costoDelivery, 2);

        return [
            'id' => (int) $pedido->id,
            'codigo' => (string) $pedido->codigo,
            'estado' => $pedido->estado->value,
            'estado_label' => $pedido->estado->getLabel(),
            'estado_color' => $pedido->estado->getColor(),
            'subtotal' => $subtotal,
            'costo_delivery' => $costoDelivery,
            'total' => $total,
            'moneda' => 'C$',
            'abierto_en' => $pedido->abierto_en?->format('d/m/Y H:i') ?? $pedido->created_at?->format('d/m/Y H:i'),
            'notas' => $pedido->notas,
            'es_delivery' => $detalleDelivery['es_delivery'],
            'mesa' => $pedido->mesa?->nombre,
            'items' => $items,
        ];
    }
}
