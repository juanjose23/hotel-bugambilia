<?php

declare(strict_types=1);

namespace App\BusinessLogic\Ventas;

use App\Enums\Cuentas\EstadoVenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaDetalle;

/**
 * Construye la fotografía histórica que congela una venta al cerrar su cuenta.
 *
 * Los impuestos, descuentos y otros cargos se gestionan a nivel de cuenta
 * (cuenta_cargos), por lo que el detalle de venta captura únicamente el
 * subtotal por línea; el desglose agregado queda en la cabecera.
 */
final class ConstruirFotografiaVenta
{
    /**
     * @param  array<string, mixed>|null  $datosFiscales
     * @return array<string, mixed>
     */
    public function cabecera(Cuenta $cuenta, string $numeroVenta, ?int $usuarioId, ?array $datosFiscales): array
    {
        return [
            'numero_venta' => $numeroVenta,
            'cuenta_id' => $cuenta->id,
            'cliente_id' => $cuenta->cliente_id,
            'moneda_id' => $cuenta->moneda_id,
            'subtotal' => max(0.0, (float) $cuenta->subtotal),
            'descuento_total' => max(0.0, (float) $cuenta->descuento_total),
            'impuesto_total' => max(0.0, (float) $cuenta->impuesto_total),
            'servicio_total' => max(0.0, (float) $cuenta->cargo_servicio_total),
            'propina_total' => max(0.0, (float) $cuenta->propina_total),
            'recargo_total' => max(0.0, (float) $cuenta->recargo_total),
            'total' => max(0.0, (float) $cuenta->total),
            'estado' => EstadoVenta::Emitida,
            'datos_fiscales' => $datosFiscales,
            'creada_por' => $usuarioId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detalle(CuentaDetalle $detalle): array
    {
        return [
            'concepto' => $detalle->concepto,
            'cantidad' => $detalle->cantidad,
            'precio_unitario' => $detalle->precio_unitario,
            'subtotal' => $detalle->subtotal,
            'descuento' => 0,
            'impuesto' => 0,
            'servicio' => 0,
            'propina' => 0,
            'recargo' => 0,
            'total_linea' => $detalle->subtotal,
            'origen_type' => $detalle->origen_type,
            'origen_id' => $detalle->origen_id,
        ];
    }
}
