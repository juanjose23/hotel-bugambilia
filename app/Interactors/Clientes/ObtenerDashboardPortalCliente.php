<?php

declare(strict_types=1);

namespace App\Interactors\Clientes;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class ObtenerDashboardPortalCliente
{
    /**
     * @return array<string, mixed>
     */
    public function ejecutar(User $user): array
    {
        $cliente = $user->persona?->cliente;
        $clienteId = $cliente?->id;
        $email = $user->email;

        /** @var Builder<Reserva> $query */
        $query = Reserva::with([
            'habitacion.categoria',
            'habitacion.imagenes',
            'espacio',
            'moneda',
            'cuentas.detalles',
            'cuentas.pagos',
        ])->orderBy('id', 'desc');

        $reservas = $query->where(function (Builder $q) use ($clienteId, $email): void {
            if ($clienteId !== null) {
                $q->where('cliente_id', $clienteId);
            }
            if (is_string($email) && trim($email) !== '') {
                if ($clienteId !== null) {
                    $q->orWhere('email_cliente', trim($email));
                } else {
                    $q->where('email_cliente', trim($email));
                }
            }
        })->get();

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

        return [
            'cliente' => $this->mapearCliente($user, $cliente),
            'estancia_activa' => $proximaEstancia !== null ? $this->mapearReservaResumen($proximaEstancia) : null,
            'reservas_activas' => $activas->map(fn (Reserva $r) => $this->mapearReservaResumen($r))->all(),
            'historial_reservas' => $historial->map(fn (Reserva $r) => $this->mapearReservaResumen($r))->all(),
            'estadisticas' => [
                'total_reservas' => $reservas->count(),
                'activas' => $activas->count(),
                'completadas' => $historial->where('estado', EstadoReserva::CHECKED_OUT)->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapearCliente(User $user, ?Cliente $cliente): array
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
    public function mapearReservaResumen(Reserva $r): array
    {
        $hab = $r->habitacion;
        $espacio = $r->espacio;
        $imagen = $hab !== null && $hab->imagenes->first() !== null ? (string) $hab->imagenes->first()->url : null;
        $recursoNombre = $hab !== null ? (string) $hab->nombre : ($espacio !== null ? (string) $espacio->nombre : 'Hospedaje');
        $categoriaNombre = $hab !== null && $hab->categoria !== null ? (string) $hab->categoria->nombre : 'Suite';

        return [
            'id' => (int) $r->id,
            'codigo_reserva' => (string) $r->codigo_reserva,
            'estado' => $r->estado->value,
            'estado_label' => $r->estado->getLabel(),
            'tipo_reserva' => $r->tipo_reserva->value,
            'tipo_reserva_label' => $r->tipo_reserva->getLabel(),
            'fecha_check_in' => $r->fecha_check_in?->format('Y-m-d'),
            'fecha_check_out' => $r->fecha_check_out?->format('Y-m-d'),
            'noches' => $r->noches,
            'adultos' => $r->adultos,
            'ninos' => $r->ninos,
            'total' => (float) $r->total,
            'total_pagado' => (float) $r->total_pagado,
            'saldo' => (float) $r->saldo,
            'moneda_simbolo' => $r->moneda !== null ? (string) $r->moneda->simbolo : '$',
            'recurso' => [
                'id' => $hab !== null ? (int) $hab->id : ($espacio !== null ? (int) $espacio->id : null),
                'nombre' => $recursoNombre,
                'categoria' => $categoriaNombre,
                'imagen' => $imagen,
            ],
            'puede_cancelar' => in_array($r->estado, [EstadoReserva::PENDIENTE, EstadoReserva::CONFIRMADA], true),
            'url_voucher' => route('reservas.voucher', [
                'reserva' => $r->id,
                'codigo' => $r->codigo_reserva,
            ]),
        ];
    }
}
