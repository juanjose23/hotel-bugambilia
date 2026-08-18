<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Events\Reservas\ReservaCancelada;
use App\Interactors\Cuentas\Gestion\AnularCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class CancelarReservaPorPagoFallido
{
    public function __construct(
        private ReservaRepositorioInterface $reservas,
        private CuentaRepositorioInterface $cuentas,
        private AnularCuenta $anularCuenta,
    ) {}

    /**
     * Cancela una reserva cuando el pago Stripe falla (ej. tarjeta no admite divisa).
     * No aplica penalización ni reembolso porque no se capturó dinero.
     */
    public function ejecutar(PagoTransaccion $transaccion): void
    {
        /** @var Reserva|null $reserva */
        $reserva = $transaccion->reserva;

        if ($reserva === null) {
            return;
        }

        if ($reserva->estado !== EstadoReserva::PENDIENTE) {
            return;
        }

        DB::transaction(function () use ($reserva): void {
            $estadoAnterior = $reserva->estado;

            foreach ($this->reservas->detallesDe($reserva) as $detalle) {
                $this->reservas->actualizarDetalle($detalle, [
                    'estado' => EstadoReservaDetalle::CANCELADO,
                    'cancelado_at' => now(),
                ]);
            }

            /** @var Cuenta|null $cuenta */
            $cuenta = $this->cuentas->primeraCuentaDeReserva($reserva->id);

            if ($cuenta !== null && $cuenta->estado === EstadoCuenta::ABIERTA) {
                $this->anularCuenta->ejecutar($cuenta, 'Pago rechazado por pasarela de cobro');
            }

            $reserva->crearEntradaBitacora('cancelacion', [
                'motivo' => 'Pago rechazado por pasarela de cobro',
                'es_no_show' => false,
                'politica' => [
                    'porcentaje_penalizacion' => 0,
                    'monto_penalizacion' => 0,
                ],
                'pagado_al_cancelar' => 0,
                'monto_reembolso' => 0,
                'cancelado_at' => now()->toISOString(),
                'usuario_id' => null,
            ]);

            $this->reservas->actualizar($reserva, [
                'estado' => EstadoReserva::CANCELADA,
                'total' => 0,
                'total_pagado' => 0,
                'saldo' => 0,
            ]);

            $this->reservas->registrarHistorial(
                $reserva,
                $estadoAnterior,
                EstadoReserva::CANCELADA,
                'Cancelación automática: pago rechazado por pasarela de cobro',
            );

            ReservaCancelada::dispatch($reserva, 'Pago rechazado por pasarela de cobro');
        });
    }
}
