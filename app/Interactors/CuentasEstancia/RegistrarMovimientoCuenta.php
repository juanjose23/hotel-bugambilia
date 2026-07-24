<?php

declare(strict_types=1);

namespace App\Interactors\CuentasEstancia;

use App\BusinessLogic\CuentasEstancia\ValidarCuentaEstancia;
use App\Enums\Estancias\TipoMovimientoCuenta;
use App\Events\Estancias\MovimientoCuentaRegistrado;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Estancias\MovimientoCuentaEstancia;
use Illuminate\Support\Facades\DB;

final class RegistrarMovimientoCuenta
{
    public function __construct(
        private readonly ValidarCuentaEstancia $validarCuenta,
    ) {}

    /**
     * @param  array<string, mixed>|null  $metadatos
     */
    public function ejecutar(
        CuentaEstancia $cuenta,
        TipoMovimientoCuenta $tipo,
        string $concepto,
        float $monto,
        ?int $usuarioId = null,
        ?string $origenType = null,
        ?int $origenId = null,
        ?array $metadatos = null,
    ): MovimientoCuentaEstancia {
        $this->validarCuenta->puedeRegistrarMovimiento($cuenta);

        if ($tipo === TipoMovimientoCuenta::CARGO) {
            $this->validarCuenta->validarLimiteAutorizado($cuenta, $monto);
        }

        return DB::transaction(function () use ($cuenta, $tipo, $concepto, $monto, $usuarioId, $origenType, $origenId, $metadatos): MovimientoCuentaEstancia {
            $movimiento = $cuenta->movimientos()->create([
                'tipo' => $tipo,
                'concepto' => $concepto,
                'monto' => abs($monto),
                'origen_type' => $origenType,
                'origen_id' => $origenId,
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
