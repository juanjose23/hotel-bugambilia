<?php

declare(strict_types=1);

namespace App\Actions\Compras\Devoluciones;

use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Queries\Compras\Devoluciones\ObtenerDevolucionReporteQuery;
use App\Support\Barcode\BarcodeGenerator;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;

final class GenerarReporteDevolucionPdfAction
{
    use GuardaReporte;

    public function __construct(
        private readonly BarcodeGenerator $barcodeGenerator,
        private readonly ObtenerDevolucionReporteQuery $query,
    ) {}

    public function ejecutar(DevolucionCompra $devolucion): PdfDocumento
    {
        $devolucionConRelaciones = $this->query->ejecutar($devolucion->id) ?? $devolucion;

        $codigoReporte = 'HTB-COM-005';
        $nombreReporte = 'Devolución a Proveedor';
        $datosHotel = HotelInfo::getBaseData();
        $barcodeBase64 = $this->barcodeGenerator->base64(
            code: (string) $devolucion->id,
            height: 100,
            widthFactor: 4,
        );

        $layout = new LayoutPdf;

        $pdf = Pdf::loadView('reports.compras.devoluciones.devolucion-compra', [
            'record' => $devolucionConRelaciones,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'barcodeBase64' => $barcodeBase64,
            'datosHotel' => $datosHotel,
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
        ])->setPaper('letter', 'portrait');

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'devolucion_id' => $devolucion->id,
                'orden_compra_id' => $devolucion->orden_compra_id,
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
