<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

final class ObtenerReservasClienteLanding
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function ejecutar(?string $emailOrCode = null): array
    {
        $query = $this->construirQuery($emailOrCode);

        if ($query === null) {
            return [];
        }

        return $query->get()
            ->map(fn (Reserva $r): array => $this->reservaToArray($r))
            ->values()
            ->all();
    }

    // -------------------------------------------------------------------------
    // Query
    // -------------------------------------------------------------------------

    /**
     * @return ?Builder<Reserva>
     */
    private function construirQuery(?string $emailOrCode): ?Builder
    {
        $user = Auth::user();
        /** @var Builder<Reserva> $query */
        $query = Reserva::with([
            'cliente.persona',
            'habitacion.categoria',
            'habitacion.servicioAsignaciones.servicio',
            'espacio',
            'moneda',
        ])->orderBy('id', 'desc');

        if ($user) {
            $clienteId = $user->persona?->cliente?->id;
            $emailUser = $user->email;

            return $query->where(function (Builder $q) use ($clienteId, $emailUser): void {
                if ($clienteId !== null) {
                    $q->where('cliente_id', $clienteId);
                }
                if (is_string($emailUser) && trim($emailUser) !== '') {
                    if ($clienteId !== null) {
                        $q->orWhere('email_cliente', trim($emailUser));
                    } else {
                        $q->where('email_cliente', trim($emailUser));
                    }
                }
            });
        }

        if ($emailOrCode !== null && trim($emailOrCode) !== '') {
            $val = trim($emailOrCode);

            return $query->where(fn (Builder $q) => $q
                ->where('codigo_reserva', $val)
                ->orWhere('email_cliente', $val));
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Reserva
    // -------------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function reservaToArray(Reserva $r): array
    {
        $habitacion = $r->habitacion;
        $espacio = $r->espacio;

        return [
            'id' => $r->id,
            'codigo_reserva' => $r->codigo_reserva,
            'nombre_cliente' => $this->resolverNombreCliente($r),
            'tipo_reserva' => $r->tipo_reserva->value,
            'tipo_reserva_label' => $r->tipo_reserva->getLabel(),
            'estado' => $r->estado->getLabel(),
            'estado_label' => $r->estado->getLabel(),
            'fecha_check_in' => $r->fecha_check_in?->format('Y-m-d'),
            'fecha_check_out' => $r->fecha_check_out?->format('Y-m-d'),
            'hora_reserva' => $r->hora_reserva,
            'adultos' => $r->adultos,
            'ninos' => $r->ninos,
            'acompanantes' => $r->acompanantes ?? [],
            'total' => (float) $r->total,
            'total_pagado' => (float) ($r->total_pagado ?? 0),
            'saldo' => (float) ($r->saldo ?? 0),
            'moneda' => $r->moneda !== null ? $r->moneda->simbolo : '$',
            'habitacion' => $this->mapearHabitacion($habitacion),
            'espacio' => $espacio !== null
                ? ['id' => $espacio->id, 'nombre' => $espacio->nombre]
                : null,
            'servicios_incluidos' => $this->mapearServiciosIncluidos($r),
            'beneficios_aplicados' => [],
            'puede_cancelar' => $this->puedeCancelar($r),
            'url_voucher' => sprintf(
                '/reservas/%d/voucher?codigo=%s',
                $r->id,
                urlencode((string) $r->codigo_reserva),
            ),
            'created_at' => $r->created_at?->format('d/m/Y'),
        ];
    }

    private function puedeCancelar(Reserva $reserva): bool
    {
        return in_array($reserva->estado, [
            EstadoReserva::PENDIENTE,
            EstadoReserva::CONFIRMADA,
        ], true);
    }

    private function resolverNombreCliente(Reserva $reserva): ?string
    {
        $nombreCliente = $reserva->cliente?->nombre_completo;

        if (is_string($nombreCliente) && trim($nombreCliente) !== '') {
            return $nombreCliente;
        }

        return $reserva->nombre_cliente;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapearHabitacion(?Habitacion $habitacion): ?array
    {
        if ($habitacion === null) {
            return null;
        }

        $categoria = $habitacion->categoria;

        return [
            'id' => $habitacion->id,
            'nombre' => $habitacion->nombre,
            'categoria' => $categoria !== null ? $categoria->nombre : null,
            'imagen_principal' => null,
        ];
    }

    /**
     * Servicios incluidos en el reservable de la reserva.
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapearServiciosIncluidos(Reserva $r): array
    {
        $habitacion = $r->habitacion;
        if ($habitacion === null) {
            return [];
        }

        return $habitacion->servicioAsignaciones
            ->map(function ($asignacion): ?array {
                $servicio = $asignacion->servicio;
                if ($servicio === null) {
                    return null;
                }

                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
