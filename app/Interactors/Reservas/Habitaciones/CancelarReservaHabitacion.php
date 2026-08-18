<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\Cuentas\CalcularReembolsoCancelacion;
use App\BusinessLogic\Reservas\CalcularPenalizacionCancelacion;
use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\BusinessLogic\Reservas\ResultadoPenalizacion;
use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use App\Enums\Facturacion\EventoWebhookStripe;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Shared\EstadoGeneral;
use App\Events\Reservas\ReservaCancelada;
use App\Interactors\Cuentas\Cobros\ReembolsarPagoCuenta;
use App\Interactors\Cuentas\Gestion\AnularCuenta;
use App\Interactors\Facturacion\Stripe\ConfirmarPagoStripeReserva;
use App\Interactors\Facturacion\Stripe\ReembolsarPagoStripeReserva;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\ObtenerTransaccionesPagoReservaQuery;
use App\Repository\Queries\Reservas\ObtenerTransaccionesStripePendientesReservaQuery;
use App\WebServices\Stripe\StripePaymentIntentClient;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class CancelarReservaHabitacion
{
    public function __construct(
        private CalcularPenalizacionCancelacion $calcularPenalizacion,
        private CalcularReembolsoCancelacion $calcularReembolso,
        private ReembolsarPagoCuenta $reembolsarPago,
        private AnularCuenta $anularCuenta,
        private ReembolsarPagoStripeReserva $reembolsarPagoStripe,
        private StripePaymentIntentClient $stripe,
        private ConfirmarPagoStripeReserva $confirmarPagoStripe,
        private ReservaRepositorioInterface $reservas,
        private CuentaRepositorioInterface $cuentas,
        private ObtenerTransaccionesPagoReservaQuery $totalTransacciones,
        private ObtenerTransaccionesStripePendientesReservaQuery $transaccionesStripePendientes,
    ) {}

    public function ejecutar(CancelarReservaHabitacionData $data, bool $esNoShow = false): Reserva
    {
        return DB::transaction(function () use ($data, $esNoShow): Reserva {
            $reserva = $this->reservas->obtenerPorIdConLock($data->reservaId);

            if (in_array($reserva->estado, [EstadoReserva::CANCELADA, EstadoReserva::CHECKED_OUT, EstadoReserva::NO_SHOW], true)) {
                throw new DomainException("La reserva #{$reserva->codigo_reserva} no se puede cancelar desde su estado actual ({$reserva->estado->getLabel()}).");
            }

            if ($this->reservas->tieneEstanciaActiva($reserva)) {
                throw new DomainException("No se puede cancelar la reserva #{$reserva->codigo_reserva} porque posee estancias activas.");
            }

            $estadoAnterior = $reserva->estado;
            $nuevoEstado = $esNoShow ? EstadoReserva::NO_SHOW : EstadoReserva::CANCELADA;

            $this->cancelarDetalles($reserva);

            $resultado = $this->calcularPenalizacion->ejecutar($reserva, now(), $esNoShow);
            $montoPenalizacion = $data->montoPenalizacion ?? $resultado->monto;

            $this->sincronizarPagosStripeCapturados($reserva);
            $reserva->refresh();

            /** @var Cuenta|null $cuenta */
            $cuenta = $this->cuentas->primeraCuentaDeReserva($reserva->id);
            $totalPagadoInicial = $this->obtenerTotalPagadoInicial($reserva, $cuenta);
            $montoReembolso = $this->calcularReembolso->ejecutar($totalPagadoInicial, $montoPenalizacion);

            $this->crearCargoPenalizacion($cuenta, $reserva, $montoPenalizacion, $data);

            [$reembolsosStripe, $reembolsosPendientes] = $this->procesarReembolsos(
                $reserva, $cuenta, $montoReembolso, $data,
            );

            if ($cuenta !== null) {
                $this->anularCuenta->ejecutar($cuenta, $data->motivo, $data->usuarioId);
            }

            $this->registrarBitacoraCancelacion(
                $reserva, $data, $esNoShow, $resultado, $totalPagadoInicial,
                $montoPenalizacion, $montoReembolso, $reembolsosStripe, $reembolsosPendientes,
            );

            $this->reservas->actualizar($reserva, [
                'estado' => $nuevoEstado,
                'total' => $montoPenalizacion,
                'total_pagado' => min((float) $reserva->total_pagado, $montoPenalizacion),
                'saldo' => 0.00,
            ]);

            $this->reservas->registrarHistorial(
                $reserva, $estadoAnterior, $nuevoEstado,
                ($esNoShow ? 'No-Show: ' : 'Cancelación: ').$data->motivo,
                $data->usuarioId,
            );

            ReservaCancelada::dispatch($reserva, $data->motivo);

            return $reserva->refresh();
        });
    }

    private function cancelarDetalles(Reserva $reserva): void
    {
        foreach ($this->reservas->detallesDe($reserva) as $detalle) {
            $this->reservas->actualizarDetalle($detalle, [
                'estado' => EstadoReservaDetalle::CANCELADO,
                'cancelado_at' => now(),
            ]);
        }
    }

    /**
     * Calcula el total pagado inicial comparando cuenta, reserva y transacciones.
     */
    private function obtenerTotalPagadoInicial(Reserva $reserva, ?Cuenta $cuenta): float
    {
        $totalTransacciones = $this->totalTransacciones->ejecutar($reserva->id);

        $stripeMeta = $reserva->ultimaEntradaBitacora('stripe') ?? [];
        $montoStripeMeta = isset($stripeMeta['amount']) && is_numeric($stripeMeta['amount'])
            ? (float) $stripeMeta['amount'] / 100
            : 0.0;

        return max(
            (float) ($cuenta !== null ? $cuenta->total_pagado : 0.0),
            (float) $reserva->total_pagado,
            $totalTransacciones,
            $montoStripeMeta,
        );
    }

    private function crearCargoPenalizacion(
        ?Cuenta $cuenta,
        Reserva $reserva,
        float $montoPenalizacion,
        CancelarReservaHabitacionData $data,
    ): void {
        if ($cuenta === null || $montoPenalizacion <= 0.0) {
            return;
        }

        $this->cuentas->crearCuentaCargo($cuenta, [
            'moneda_id' => $cuenta->moneda_id,
            'tipo' => TipoCargo::Recargo->value,
            'codigo' => "PEN-RES-{$reserva->id}",
            'nombre' => 'Penalización por cancelación',
            'modo_calculo' => ModoCargo::Manual->value,
            'valor' => 0,
            'base_calculo' => BaseCalculo::BaseManual->value,
            'base_monto' => $montoPenalizacion,
            'monto' => $montoPenalizacion,
            'aplicado_por' => $data->usuarioId,
            'estado' => EstadoGeneral::Activo->value,
        ]);
    }

    /**
     * Procesa reembolsos de excedente (Stripe + pendientes administración).
     *
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private function procesarReembolsos(
        Reserva $reserva,
        ?Cuenta $cuenta,
        float $montoReembolso,
        CancelarReservaHabitacionData $data,
    ): array {
        $reembolsosStripe = [];
        $reembolsosPendientesAdministracion = [];

        if ($montoReembolso <= 0.0) {
            return [$reembolsosStripe, $reembolsosPendientesAdministracion];
        }

        if ($data->marcarReembolsoPendiente) {
            $reembolsosPendientesAdministracion[] = [
                'monto' => round($montoReembolso, 2),
                'estado' => 'pendiente_administracion',
                'motivo' => 'stripe_no_disponible',
                'registrado_at' => now()->toISOString(),
            ];

            return [$reembolsosStripe, $reembolsosPendientesAdministracion];
        }

        $reembolsosStripe = $this->reembolsarPagoStripe->ejecutar(
            $reserva, $montoReembolso, $data->motivo, $data->usuarioId, $data->reembolsoStripeEstricto,
        );

        $montoReembolsadoStripe = round(array_sum(array_map(
            static fn (array $reembolso): float => (float) $reembolso['monto'],
            $reembolsosStripe,
        )), 2);

        $pagoCuentaIdsPasarelas = array_values(array_filter(
            array_map(static fn (array $r): ?int => is_numeric($r['pago_cuenta_id'] ?? null) ? (int) $r['pago_cuenta_id'] : null, $reembolsosStripe),
        ));

        if ($cuenta !== null) {
            $this->reembolsarPago->ejecutar(
                cuenta: $cuenta,
                montoReembolso: $montoReembolsadoStripe,
                motivo: $data->motivo,
                usuarioId: $data->usuarioId,
                pagoCuentaIds: $pagoCuentaIdsPasarelas !== [] ? $pagoCuentaIdsPasarelas : null,
            );

            $montoReembolsoManual = round($montoReembolso - $montoReembolsadoStripe, 2);
            if ($montoReembolsoManual > 0.0) {
                $this->reembolsarPago->ejecutar($cuenta, $montoReembolsoManual, $data->motivo, $data->usuarioId);
            }
        }

        return [$reembolsosStripe, $reembolsosPendientesAdministracion];
    }

    /**
     * Registra la entrada de bitácora de cancelación con todos los datos del proceso.
     *
     * @param  array<int, array<string, mixed>>  $reembolsosStripe
     * @param  array<int, array<string, mixed>>  $reembolsosPendientes
     */
    private function registrarBitacoraCancelacion(
        Reserva $reserva,
        CancelarReservaHabitacionData $data,
        bool $esNoShow,
        ResultadoPenalizacion $resultado,
        float $totalPagadoInicial,
        float $montoPenalizacion,
        float $montoReembolso,
        array $reembolsosStripe,
        array $reembolsosPendientes,
    ): void {
        $reserva->crearEntradaBitacora('cancelacion', [
            'motivo' => $data->motivo,
            'es_no_show' => $esNoShow,
            'politica' => [
                'porcentaje_penalizacion' => $resultado->porcentaje,
                'monto_penalizacion' => round($montoPenalizacion, 2),
            ],
            'pagado_al_cancelar' => round($totalPagadoInicial, 2),
            'monto_reembolso' => round($montoReembolso, 2),
            'reembolsos_stripe' => $reembolsosStripe,
            'reembolsos_pendientes_administracion' => $reembolsosPendientes,
            'cancelado_at' => now()->toISOString(),
            'usuario_id' => $data->usuarioId,
        ]);
    }

    private function sincronizarPagosStripeCapturados(Reserva $reserva): void
    {
        $transaccionesPendientes = $this->transaccionesStripePendientes->ejecutar($reserva->id);

        foreach ($transaccionesPendientes as $transaccion) {
            $paymentIntentId = (string) $transaccion->referencia_pasarela;
            try {
                $paymentIntent = $this->stripe->obtenerPaymentIntent($paymentIntentId);

                if (($paymentIntent['status'] ?? null) !== 'succeeded') {
                    continue;
                }

                $this->confirmarPagoStripe->ejecutar($paymentIntentId, [
                    'id' => "sincronizacion-cancelacion-{$paymentIntentId}",
                    'type' => EventoWebhookStripe::PaymentIntentSucceeded->value,
                    'data' => [
                        'object' => $paymentIntent,
                    ],
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
