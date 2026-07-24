<?php

declare(strict_types=1);

namespace App\Interactors\CuentasEstancia;

use App\BusinessLogic\CuentasEstancia\ValidarCuentaEstancia;
use App\Enums\Estancias\CategoriaConsumo;
use App\Enums\Estancias\EstadoMovimientoCuenta;
use App\Enums\Estancias\TipoMovimientoCuenta;
use App\Events\Estancias\MovimientoCuentaRegistrado;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Estancias\MovimientoCuentaEstancia;
use Illuminate\Support\Facades\DB;

final class RegistrarConsumoCuenta
{
    public function __construct(
        private readonly ValidarCuentaEstancia $validarCuenta = new ValidarCuentaEstancia,
    ) {}

    /**
     * Interactor unificado reutilizable para registrar consumos y cargos desde Restaurante, Lavandería, Minibar y Servicios.
     *
     * @param  array<string, mixed>|null  $metadatosAdicionales
     */
    public function ejecutar(
        CuentaEstancia $cuenta,
        CategoriaConsumo $categoria,
        string $concepto,
        float $precioUnitario,
        int $cantidad = 1,
        float $impuesto = 0.0,
        float $descuento = 0.0,
        ?int $usuarioId = null,
        ?string $moduloOrigen = null,
        ?string $origenType = null,
        ?int $origenId = null,
        EstadoMovimientoCuenta $estado = EstadoMovimientoCuenta::CONFIRMADO,
        ?array $metadatosAdicionales = null,
    ): MovimientoCuentaEstancia {
        // Validar que la cuenta esté en estado ABIERTA
        $this->validarCuenta->puedeRegistrarMovimiento($cuenta);

        $subtotal = round($precioUnitario * max(1, $cantidad), 2);
        $totalCalculado = round($subtotal + $impuesto - $descuento, 2);

        // Validar límite autorizado si aplica
        $this->validarCuenta->validarLimiteAutorizado($cuenta, $totalCalculado);

        return DB::transaction(function () use (
            $cuenta,
            $categoria,
            $concepto,
            $precioUnitario,
            $cantidad,
            $impuesto,
            $descuento,
            $totalCalculado,
            $usuarioId,
            $moduloOrigen,
            $origenType,
            $origenId,
            $estado,
            $metadatosAdicionales
        ): MovimientoCuentaEstancia {
            $metadatos = array_merge([
                'categoria' => $categoria->value,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'impuesto' => $impuesto,
                'descuento' => $descuento,
                'modulo_origen' => $moduloOrigen ?? 'recepcion',
                'estado_movimiento' => $estado->value,
            ], $metadatosAdicionales ?? []);

            $movimiento = $cuenta->movimientos()->create([
                'tipo' => TipoMovimientoCuenta::CARGO,
                'concepto' => $concepto,
                'monto' => $totalCalculado,
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
