<?php

declare(strict_types=1);

namespace App\Interactors\CuentasEstancia;

use App\BusinessLogic\CuentasEstancia\ValidarCuentaEstancia;
use App\Enums\Estancias\EstadoPago;
use App\Enums\Estancias\MetodoPago;
use App\Enums\Estancias\TipoMovimientoCuenta;
use App\Events\Estancias\MovimientoCuentaRegistrado;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Estancias\MovimientoCuentaEstancia;
use DomainException;
use Illuminate\Support\Facades\DB;

final class RegistrarPagoCuenta
{
    public function __construct(
        private readonly ValidarCuentaEstancia $validarCuenta = new ValidarCuentaEstancia,
    ) {}

    /**
     * Registra un pago, anticipo o abono a favor de la cuenta de estancia.
     *
     * @param  array<string, mixed>|null  $metadatosAdicionales
     */
    public function ejecutar(
        CuentaEstancia $cuenta,
        MetodoPago $metodoPago,
        float $monto,
        string $concepto = 'Abono / Pago de Cuenta',
        ?string $referencia = null,
        ?string $bancoOrigen = null,
        ?int $usuarioId = null,
        EstadoPago $estado = EstadoPago::APLICADO,
        ?array $metadatosAdicionales = null,
    ): MovimientoCuentaEstancia {
        if ($monto <= 0) {
            throw new DomainException('El monto del pago debe ser mayor a cero.');
        }

        // Validar que la cuenta permita movimientos
        $this->validarCuenta->puedeRegistrarMovimiento($cuenta);

        return DB::transaction(function () use (
            $cuenta,
            $metodoPago,
            $monto,
            $concepto,
            $referencia,
            $bancoOrigen,
            $usuarioId,
            $estado,
            $metadatosAdicionales
        ): MovimientoCuentaEstancia {
            $metadatos = array_merge([
                'metodo_pago' => $metodoPago->value,
                'referencia_transaccion' => $referencia,
                'banco_origen' => $bancoOrigen,
                'estado_pago' => $estado->value,
            ], $metadatosAdicionales ?? []);

            $movimiento = $cuenta->movimientos()->create([
                'tipo' => TipoMovimientoCuenta::PAGO,
                'concepto' => $concepto,
                'monto' => round($monto, 2),
                'usuario_id' => $usuarioId,
                'metadatos' => $metadatos,
            ]);

            $this->recalcularSaldos($cuenta);

            MovimientoCuentaRegistrado::dispatch($movimiento);

            return $movimiento;
        });
    }

    private function recalcularSaldos(CuentaEstancia $cuenta): void
    {
        $cuenta->refresh();

        $totalCargos = (float) $cuenta->movimientos()
            ->where('tipo', TipoMovimientoCuenta::CARGO)
            ->sum('monto');

        $totalPagos = (float) $cuenta->movimientos()
            ->where('tipo', TipoMovimientoCuenta::PAGO)
            ->sum('monto');

        $totalDescuentos = (float) $cuenta->movimientos()
            ->where('tipo', TipoMovimientoCuenta::DESCUENTO)
            ->sum('monto');

        $totalAjustes = (float) $cuenta->movimientos()
            ->where('tipo', TipoMovimientoCuenta::AJUSTE)
            ->sum('monto');

        $saldo = $totalCargos - $totalPagos - $totalDescuentos + $totalAjustes;

        $cuenta->update([
            'total_cargos' => $totalCargos,
            'total_pagos' => $totalPagos,
            'saldo' => max(0, round($saldo, 2)),
        ]);
    }
}
