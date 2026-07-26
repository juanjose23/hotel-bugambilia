<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Events\Cuentas\PagoCuentaRegistrado;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Registra un pago/abono a favor de una Cuenta y actualiza el saldo.
 * Reemplaza: RegistrarPagoCuenta (Estancias) + PagoRestaurante.
 */
final class RegistrarPagoCuenta
{
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
            $pago = $cuenta->pagos()->create([
                'forma_pago' => $metodoPago,
                'moneda_id' => $monedaId ?? $cuenta->moneda_id,
                'estado' => $estado,
                'monto' => round($monto, 2),
                'propina' => round($propina, 2),
                'referencia_transaccion' => $referenciaTransaccion,
                'observaciones' => $observaciones,
                'usuario_id' => $usuarioId,
            ]);

            $this->recalcularSaldos($cuenta);

            PagoCuentaRegistrado::dispatch($pago);

            return $pago;
        });
    }

    /** Recalcula total_pagado y saldo en la cabecera Cuenta */
    private function recalcularSaldos(Cuenta $cuenta): void
    {
        $cuenta->refresh();

        $totalPagado = (float) $cuenta->pagos()
            ->where('estado', EstadoPago::APLICADO)
            ->sum('monto');

        $saldo = max(0, round((float) $cuenta->total - $totalPagado, 2));

        $cuenta->update([
            'total_pagado' => $totalPagado,
            'saldo' => $saldo,
        ]);
    }
}
