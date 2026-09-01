<?php

declare(strict_types=1);

namespace App\Interactors\Clientes;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use DomainException;

final class ObtenerDetalleReservaPortal
{
    /**
     * @return array<string, mixed>
     */
    public function ejecutar(int $reservaId, ?User $user = null, ?string $codigo = null): array
    {
        /** @var Reserva|null $reserva */
        $reserva = Reserva::with([
            'habitacion.categoria',
            'habitacion.imagenes',
            'habitacion.servicioAsignaciones.servicio',
            'espacio',
            'moneda',
            'cuentas.detalles.moneda',
            'cuentas.cargos.moneda',
            'cuentas.pagos.moneda',
            'detalles.huespedes',
        ])->find($reservaId);

        if ($reserva === null) {
            throw new DomainException("La reserva #{$reservaId} no existe.");
        }

        // Validación de pertenencia si no es código válido ni usuario dueño
        $esValidoCodigo = $codigo !== null && trim($codigo) !== '' && $reserva->codigo_reserva === trim($codigo);
        $esAdmin = $user !== null && ($user->is_admin || $user->can('View:Reserva'));
        $esPropietario = $user !== null && (
            ($user->persona?->cliente?->id !== null && $user->persona->cliente->id === $reserva->cliente_id)
            || (is_string($user->email) && strcasecmp($user->email, (string) $reserva->email_cliente) === 0)
        );

        if (! $esValidoCodigo && ! $esAdmin && ! $esPropietario && $user !== null) {
            throw new DomainException('No tienes permiso para consultar esta reservación.');
        }

        $hab = $reserva->habitacion;
        /** @var Cuenta|null $cuentaPrincipal */
        $cuentaPrincipal = $reserva->cuentas->first();

        return [
            'id' => $reserva->id,
            'codigo_reserva' => $reserva->codigo_reserva,
            'estado' => $reserva->estado->value,
            'estado_label' => $reserva->estado->getLabel(),
            'tipo_reserva' => $reserva->tipo_reserva->value,
            'tipo_reserva_label' => $reserva->tipo_reserva->getLabel(),
            'nombre_cliente' => $reserva->nombre_cliente,
            'email_cliente' => $reserva->email_cliente,
            'telefono_cliente' => $reserva->telefono_cliente,
            'fecha_check_in' => $reserva->fecha_check_in?->format('Y-m-d'),
            'fecha_check_out' => $reserva->fecha_check_out?->format('Y-m-d'),
            'hora_reserva' => $reserva->hora_reserva,
            'noches' => $reserva->noches,
            'adultos' => $reserva->adultos,
            'ninos' => $reserva->ninos,
            'total' => (float) $reserva->total,
            'total_pagado' => (float) $reserva->total_pagado,
            'saldo' => (float) $reserva->saldo,
            'moneda' => [
                'id' => $reserva->moneda?->id,
                'codigo' => $reserva->moneda !== null ? (string) $reserva->moneda->codigo : 'USD',
                'simbolo' => $reserva->moneda !== null ? (string) $reserva->moneda->simbolo : '$',
                'nombre' => $reserva->moneda !== null ? (string) $reserva->moneda->nombre : 'Dólar',
            ],
            'recurso' => [
                'id' => $hab !== null ? (int) $hab->id : ($reserva->espacio !== null ? (int) $reserva->espacio->id : null),
                'nombre' => $hab !== null ? (string) $hab->nombre : ($reserva->espacio !== null ? (string) $reserva->espacio->nombre : 'Hospedaje'),
                'categoria' => $hab !== null && $hab->categoria !== null ? (string) $hab->categoria->nombre : 'Suite',
                'codigo' => $hab !== null ? $hab->codigo : ($reserva->espacio !== null ? $reserva->espacio->codigo : null),
                'imagenes' => $hab !== null ? $hab->imagenes->map(fn ($img) => ['url' => (string) $img->url])->all() : [],
                'servicios_incluidos' => $hab !== null ? $hab->servicioAsignaciones->map(fn ($sa) => [
                    'id' => $sa->servicio !== null ? (int) $sa->servicio->id : null,
                    'nombre' => $sa->servicio !== null ? (string) $sa->servicio->nombre : null,
                ])->filter(fn ($s) => $s['id'] !== null)->values()->all() : [],
            ],
            'acompanantes' => is_array($reserva->acompanantes) ? $reserva->acompanantes : [],
            'cuenta' => $cuentaPrincipal !== null ? [
                'id' => $cuentaPrincipal->id,
                'numero_cuenta' => $cuentaPrincipal->numero_cuenta,
                'estado' => $cuentaPrincipal->estado->value,
                'subtotal' => (float) $cuentaPrincipal->subtotal,
                'impuesto_total' => (float) $cuentaPrincipal->impuesto_total,
                'descuento_total' => (float) $cuentaPrincipal->descuento_total,
                'total' => (float) $cuentaPrincipal->total,
                'total_pagado' => (float) $cuentaPrincipal->total_pagado,
                'saldo' => (float) $cuentaPrincipal->saldo,
                'consumos' => $cuentaPrincipal->detalles->map(fn (CuentaDetalle $d) => [
                    'id' => $d->id,
                    'concepto' => $d->concepto,
                    'descripcion' => $d->descripcion,
                    'cantidad' => (float) $d->cantidad,
                    'precio_unitario' => (float) $d->precio_unitario,
                    'subtotal' => (float) $d->subtotal,
                    'total' => (float) $d->total,
                    'created_at' => $d->created_at?->format('d/m/Y H:i'),
                ])->all(),
                'pagos' => $cuentaPrincipal->pagos->map(fn (PagoCuenta $p) => [
                    'id' => $p->id,
                    'monto' => (float) $p->monto,
                    'forma_pago' => $p->forma_pago->value,
                    'estado' => $p->estado->value,
                    'fecha_pago' => $p->created_at->format('d/m/Y H:i'),
                ])->all(),
            ] : null,
            'puede_cancelar' => in_array($reserva->estado, [EstadoReserva::PENDIENTE, EstadoReserva::CONFIRMADA], true),
            'puede_solicitar_servicios' => in_array($reserva->estado, [EstadoReserva::CONFIRMADA, EstadoReserva::CHECKED_IN], true),
            'url_voucher' => route('reservas.voucher', [
                'reserva' => $reserva->id,
                'codigo' => $reserva->codigo_reserva,
            ]),
            'url_pago_saldo' => $reserva->saldo > 0 ? route('reservas.pago', ['reserva' => $reserva->id]) : null,
        ];
    }
}
