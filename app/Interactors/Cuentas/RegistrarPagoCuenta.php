<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Reservas\TipoPagoReserva;
use App\Events\Cuentas\PagoCuentaRegistrado;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Registra un pago/abono a favor de una Cuenta y actualiza el saldo.
 */
final class RegistrarPagoCuenta
{
    public function __construct(
        private readonly RecalcularCuenta $recalcularCuenta,
        private readonly CuentaRepositorioInterface $cuentas,
        private readonly ReservaRepositorioInterface $reservas,
    ) {}

    public function ejecutar(
        Cuenta $cuenta,
        MetodoPago $metodoPago,
        float $monto,
        float $propina = 0.0,
        EstadoPago $estado = EstadoPago::APLICADO,
        ?string $referenciaTransaccion = null,
        ?string $observaciones = null,
        ?int $monedaId = null,
        ?int $usuarioId = null,
    ): PagoCuenta {
        if ($monto <= 0) {
            throw new DomainException('El monto del pago debe ser mayor a cero.');
        }

        if (! $cuenta->estado->permiteNuevosCargos() && $cuenta->estado !== EstadoCuenta::PENDIENTE_PAGO) {
            throw new DomainException(
                "La cuenta {$cuenta->numero_cuenta} está en estado '{$cuenta->estado->getLabel()}' y no acepta pagos.",
            );
        }

        return DB::transaction(function () use (
            $cuenta, $metodoPago, $monto, $propina, $estado,
            $referenciaTransaccion, $observaciones, $monedaId, $usuarioId
        ): PagoCuenta {
            $cuentaBloqueada = $this->cuentas->bloquear((int) $cuenta->id);
            $cuentaBloqueada = $this->recalcularCuenta->ejecutar($cuentaBloqueada, $usuarioId);
            $montoRedondeado = round($monto, 2);

            if ($montoRedondeado > round((float) $cuentaBloqueada->saldo, 2)) {
                throw new DomainException('El pago no puede ser mayor que el saldo pendiente de la cuenta.');
            }

            $pago = $this->cuentas->crearPago($cuentaBloqueada, [
                'forma_pago' => $metodoPago,
                'moneda_id' => $monedaId ?? $cuentaBloqueada->moneda_id,
                'estado' => $estado,
                'monto' => $montoRedondeado,
                'propina' => round($propina, 2),
                'referencia_transaccion' => $referenciaTransaccion,
                'observaciones' => $observaciones,
                'usuario_id' => $usuarioId,
            ]);

            $cuentaActualizada = $this->recalcularCuenta->ejecutar($cuentaBloqueada, $usuarioId);

            if ($cuentaActualizada->reserva_id !== null) {
                $reserva = $this->reservas->obtenerPorId((int) $cuentaActualizada->reserva_id);
                if ($reserva !== null) {
                    $this->reservas->actualizar($reserva, [
                        'total' => $cuentaActualizada->total,
                        'total_pagado' => $cuentaActualizada->total_pagado,
                        'saldo' => $cuentaActualizada->saldo,
                        'tipo_pago' => (float) $cuentaActualizada->saldo <= 0
                            ? TipoPagoReserva::PAGO_COMPLETO
                            : TipoPagoReserva::ABONO_50,
                    ]);
                }
            }

            PagoCuentaRegistrado::dispatch($pago);

            return $pago;
        });
    }
}
