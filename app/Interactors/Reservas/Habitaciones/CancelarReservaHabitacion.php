<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\Cuentas\CalcularReembolsoCancelacion;
use App\BusinessLogic\Reservas\CalcularPenalizacionCancelacion;
use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
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

            foreach ($this->reservas->detallesDe($reserva) as $detalle) {
                $this->reservas->actualizarDetalle($detalle, [
                    'estado' => EstadoReservaDetalle::CANCELADO,
                    'cancelado_at' => now(),
                ]);
            }

            // Calcular penalización según política de cancelación
            $resultado = $this->calcularPenalizacion->ejecutar(
                $reserva,
                now(),
                $esNoShow
            );

            $montoPenalizacion = $data->montoPenalizacion ?? $resultado->monto;

            $this->sincronizarPagosStripeCapturados($reserva);
            $reserva->refresh();

            /** @var Cuenta|null $cuenta */
            $cuenta = $this->cuentas->primeraCuentaDeReserva($reserva->id);

            $totalTransacciones = $this->totalTransacciones->ejecutar($reserva->id);

            $metaDatosRes = is_array($reserva->meta_datos) ? $reserva->meta_datos : [];
            $stripeMeta = is_array($metaDatosRes['stripe'] ?? null) ? $metaDatosRes['stripe'] : [];
            $montoStripeMeta = isset($stripeMeta['amount']) && is_numeric($stripeMeta['amount'])
                ? (float) $stripeMeta['amount'] / 100
                : 0.0;

            $totalPagadoInicial = max(
                (float) ($cuenta !== null ? $cuenta->total_pagado : 0.0),
                (float) $reserva->total_pagado,
                $totalTransacciones,
                $montoStripeMeta,
            );
            $montoReembolso = $this->calcularReembolso->ejecutar($totalPagadoInicial, $montoPenalizacion);
            $reembolsosStripe = [];
            $reembolsosPendientesAdministracion = [];

            if ($cuenta !== null && $montoPenalizacion > 0.0) {
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

            // Procesar reembolso de excedente si pagó más de la penalización (Stripe)
            if ($montoReembolso > 0.0) {
                if ($data->marcarReembolsoPendiente) {
                    // Stripe no estuvo disponible tras los reintentos: se cancela y el reembolso queda pendiente de gestión administrativa.
                    $reembolsosPendientesAdministracion[] = [
                        'monto' => round($montoReembolso, 2),
                        'estado' => 'pendiente_administracion',
                        'motivo' => 'stripe_no_disponible',
                        'registrado_at' => now()->toISOString(),
                    ];
                } else {
                    $reembolsosStripe = $this->reembolsarPagoStripe->ejecutar(
                        $reserva,
                        $montoReembolso,
                        $data->motivo,
                        $data->usuarioId,
                        $data->reembolsoStripeEstricto,
                    );
                    $montoReembolsadoStripe = round(array_sum(array_map(
                        fn (array $reembolso): float => (float) $reembolso['monto'],
                        $reembolsosStripe,
                    )), 2);

                    $pagoCuentaIdsPasarelas = array_values(array_filter(
                        array_map(fn (array $r): ?int => is_numeric($r['pago_cuenta_id'] ?? null) ? (int) $r['pago_cuenta_id'] : null, $reembolsosStripe),
                    ));

                    $montoReembolsadoPasarelas = round($montoReembolsadoStripe, 2);

                    if ($cuenta !== null) {
                        if ($montoReembolsadoPasarelas > 0.0) {
                            $this->reembolsarPago->ejecutar(
                                cuenta: $cuenta,
                                montoReembolso: $montoReembolsadoPasarelas,
                                motivo: $data->motivo,
                                usuarioId: $data->usuarioId,
                                pagoCuentaIds: $pagoCuentaIdsPasarelas !== [] ? $pagoCuentaIdsPasarelas : null,
                            );
                        }

                        $montoReembolsoManual = round($montoReembolso - $montoReembolsadoPasarelas, 2);
                        if ($montoReembolsoManual > 0.0) {
                            $this->reembolsarPago->ejecutar($cuenta, $montoReembolsoManual, $data->motivo, $data->usuarioId);
                        }
                    }
                }

                if ($cuenta !== null) {
                    $this->anularCuenta->ejecutar($cuenta, $data->motivo, $data->usuarioId);
                }
            }

            $metaDatos = is_array($reserva->meta_datos) ? $reserva->meta_datos : [];
            $metaDatos['cancelacion'] = [
                'motivo' => $data->motivo,
                'es_no_show' => $esNoShow,
                'politica' => [
                    'porcentaje_penalizacion' => $resultado->porcentaje,
                    'monto_penalizacion' => round($montoPenalizacion, 2),
                ],
                'pagado_al_cancelar' => round($totalPagadoInicial, 2),
                'monto_reembolso' => round($montoReembolso, 2),
                'reembolsos_stripe' => $reembolsosStripe,
                'reembolsos_pendientes_administracion' => $reembolsosPendientesAdministracion,
                'cancelado_at' => now()->toISOString(),
                'usuario_id' => $data->usuarioId,
            ];

            $this->reservas->actualizar($reserva, [
                'estado' => $nuevoEstado,
                'total' => $montoPenalizacion,
                'total_pagado' => min((float) $reserva->total_pagado, $montoPenalizacion),
                'saldo' => 0.00,
                'meta_datos' => $metaDatos,
            ]);

            $this->reservas->registrarHistorial(
                $reserva,
                $estadoAnterior,
                $nuevoEstado,
                ($esNoShow ? 'No-Show: ' : 'Cancelación: ').$data->motivo,
                $data->usuarioId,
            );

            ReservaCancelada::dispatch($reserva, $data->motivo);

            return $reserva->refresh();
        });
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
