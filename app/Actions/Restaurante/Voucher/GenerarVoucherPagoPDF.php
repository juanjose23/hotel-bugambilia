<?php

declare(strict_types=1);

namespace App\Actions\Restaurante\Voucher;

use App\Repository\Models\Cuentas\Cuenta;
use App\Support\HotelInfo;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GenerarVoucherPagoPDF
{
    public function ejecutar(Cuenta $cuenta): StreamedResponse
    {
        $cuenta->loadMissing(['pagos', 'pagos.moneda', 'moneda', 'cliente', 'detalles']);

        $pago = $cuenta->pagos->last();
        $totalCuenta = (float) $cuenta->total;
        $montoPagado = (float) ($pago->monto ?? 0);
        $vuelto = max(0.0, $montoPagado - $totalCuenta);
        $simboloMoneda = (string) ($cuenta->moneda->simbolo ?? 'C$');

        $datosHotel = HotelInfo::getBaseData();

        $pdf = Pdf::loadView('reports.restaurante.voucher-pago', [
            'cuenta' => $cuenta,
            'pago' => $pago,
            'totalCuenta' => $totalCuenta,
            'montoPagado' => $montoPagado,
            'vuelto' => $vuelto,
            'simboloMoneda' => $simboloMoneda,
            'datosHotel' => $datosHotel,
            'fechaEmision' => now()->format('d/m/Y H:i'),
            'codigoReporte' => 'HTB-RES-003',
            'nombreReporte' => 'Comprobante de Pago',
        ])->setPaper('letter', 'portrait');

        $nombreArchivo = "voucher-pago-{$cuenta->numero_cuenta}.pdf";

        return response()->stream(
            fn () => print ($pdf->output()),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$nombreArchivo}\"",
            ]
        );
    }
}
