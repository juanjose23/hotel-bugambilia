<?php

declare(strict_types=1);

namespace App\Actions\Restaurante\Voucher;

use App\Repository\Models\Cuentas\Cuenta;

final class GenerarVoucherPagoHTML
{
    public function ejecutar(Cuenta $cuenta): string
    {
        $cuenta->loadMissing(['pagos', 'cliente', 'estancia.habitacion', 'detalles']);

        $pago = $cuenta->pagos->last();
        $clienteNombre = $cuenta->cliente !== null
            ? $cuenta->cliente->nombre_completo
            : ($cuenta->estancia?->habitacion?->nombre !== null ? "Habitación {$cuenta->estancia->habitacion->nombre}" : 'Cliente de Mostrador');
        $metodoPago = $pago?->forma_pago->getLabel() ?? 'N/D';
        $montoPagado = $pago !== null ? (float) $pago->monto : 0.0;
        $totalCuenta = (float) $cuenta->total;
        $vuelto = max(0.0, $montoPagado - $totalCuenta);
        $simboloMoneda = (string) ($cuenta->moneda->simbolo ?? 'C$');

        return view('reports.restaurante.voucher-pago-pos', [
            'cuenta' => $cuenta,
            'pago' => $pago,
            'clienteNombre' => $clienteNombre,
            'metodoPago' => $metodoPago,
            'montoPagado' => $montoPagado,
            'totalCuenta' => $totalCuenta,
            'vuelto' => $vuelto,
            'simboloMoneda' => $simboloMoneda,
        ])->render();
    }
}
