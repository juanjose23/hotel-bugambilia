<?php

declare(strict_types=1);

namespace App\Actions\Voucher;

use App\Actions\CodigoQR\GenerarTokenQR;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Queries\Reservas\ObtenerDetallePreordenReservaQuery;
use App\Support\Barcode\BarcodeGenerator;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GenerarVoucherPDF
{
    use GuardaReporte;

    public function __construct(
        private readonly GenerarTokenQR $generarTokenQr,
        private readonly BarcodeGenerator $barcodeGenerator,
        private readonly ObtenerDetallePreordenReservaQuery $detallePreorden,
    ) {}

    public function ejecutar(Reserva $reserva): StreamedResponse
    {
        $reserva->loadMissing([
            'habitacion',
            'espacio',
            'detalles.reservable.habitacion',
            'detalles.reservable.espacio',
            'detalles.reservable.servicio',
            'serviciosAdicionalesItems.servicio',
            'huespedes',
            'estancia.cuenta',
            'cuentas.pagos',
            'moneda',
        ]);

        $datosHotel = HotelInfo::getBaseData();
        $qrResult = $this->generarTokenQr->ejecutar($reserva);
        $barcodeBase64 = $this->barcodeGenerator->base64(
            code: (string) $reserva->codigo_reserva,
            height: 70,
            widthFactor: 2,
        );

        $codigoReporte = 'HTB-RES-001';
        $nombreReporte = 'Comprobante de Emisión de Reserva';

        $layout = new LayoutPdf(
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );

        $pdf = Pdf::loadView('reports.reservas.voucher-reserva', [
            'reserva' => $reserva,
            'estadoLabel' => $reserva->estado->getLabel(),
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'qrCodeBase64' => $qrResult['qrBase64'],
            'barcodeBase64' => $barcodeBase64,
            'detallePreorden' => $this->detallePreorden->ejecutar($reserva),
            'datosHotel' => $datosHotel,
            'tokenQr' => $qrResult['token'],
            'fechaEmision' => now()->format('d/m/Y H:i'),
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenSuperiorMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenSuperiorMm,
        ])->setPaper('letter', 'portrait');

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'reserva_id' => $reserva->id,
                'codigo_reserva' => $reserva->codigo_reserva,
            ],
            pdf: $pdf,
        );

        $nombreArchivo = "voucher-reserva-{$reserva->codigo_reserva}.pdf";

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
