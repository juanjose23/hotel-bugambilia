<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas;

use App\BusinessLogic\Cuentas\ValidarCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Events\Cuentas\DetalleCuentaRegistrado;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaDetalle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Registra un cargo/débito en el detalle de la cuenta y actualiza los saldos cacheados.
 *
 * Uso desde módulos:
 *   - Restaurante: origen = PedidoItem
 *   - Estancias: origen = Estancia
 *   - Spa/Lavandería: origen = Servicio
 */
final class RegistrarDetalleCuenta
{
    public function __construct(
        private readonly ValidarCuenta $validarCuenta,
    ) {}

    /**
     * @param  array<string, mixed>|null  $metadatos
     */
    public function ejecutar(
        Cuenta $cuenta,
        string $concepto,
        float $precioUnitario,
        float $cantidad = 1.0,
        float $impuesto = 0.0,
        float $descuento = 0.0,
        ?Model $origen = null,
        ?int $espacioId = null,
        ?int $creadorId = null,
        ?array $metadatos = null,
    ): CuentaDetalle {
        $this->validarCuenta->puedeRegistrarCargo($cuenta);

        $subtotal = round($precioUnitario * max(1, $cantidad), 2);
        $total = round($subtotal + $impuesto - $descuento, 2);

        $this->validarCuenta->validarLimiteAutorizado($cuenta, $total);

        return DB::transaction(function () use (
            $cuenta, $concepto, $precioUnitario,
            $cantidad, $subtotal, $impuesto, $descuento, $total,
            $origen, $espacioId, $creadorId, $metadatos
        ): CuentaDetalle {
            $detalle = $cuenta->detalles()->create([
                'origen_type' => $origen?->getMorphClass(),
                'origen_id' => $origen?->getKey(),
                'espacio_id' => $espacioId,
                'concepto' => $concepto,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'impuesto' => $impuesto,
                'total' => $total,
                'metadatos' => $metadatos,
                'creador_id' => $creadorId,
            ]);

            $this->recalcularSaldos($cuenta);

            DetalleCuentaRegistrado::dispatch($detalle);

            return $detalle;
        });
    }

    /** Recalcula y cachea los totales en la cabecera Cuenta */
    private function recalcularSaldos(Cuenta $cuenta): void
    {
        $cuenta->refresh();

        $subtotal = (float) $cuenta->detalles()->sum('subtotal');
        $descuentoTotal = (float) $cuenta->detalles()->sum('descuento');
        $impuestoTotal = (float) $cuenta->detalles()->sum('impuesto');
        $total = round($subtotal - $descuentoTotal + $impuestoTotal, 2);

        $totalPagado = (float) $cuenta->pagos()
            ->where('estado', EstadoPago::APLICADO)
            ->sum('monto');

        $cuenta->update([
            'subtotal' => $subtotal,
            'descuento_total' => $descuentoTotal,
            'impuesto_total' => $impuestoTotal,
            'total' => $total,
            'total_pagado' => $totalPagado,
            'saldo' => max(0, round($total - $totalPagado, 2)),
        ]);
    }
}
