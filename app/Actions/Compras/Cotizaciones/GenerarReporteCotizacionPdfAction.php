<?php

declare(strict_types=1);

namespace App\Actions\Compras\Cotizaciones;

use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Queries\Compras\Cotizaciones\ObtenerCotizacionReporteQuery;
use App\Support\Barcode\BarcodeGenerator;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use Barryvdh\DomPDF\Facade\Pdf;

final class GenerarReporteCotizacionPdfAction
{
    use GuardaReporte;

    public function __construct(
        private readonly BarcodeGenerator $barcodeGenerator,
        private readonly ObtenerCotizacionReporteQuery $query,
    ) {}

    public function ejecutar(Cotizacion $cotizacion): \Barryvdh\DomPDF\PDF
    {
        $cotizacionConRelaciones = $this->query->ejecutar($cotizacion->id) ?? $cotizacion;

        $codigoReporte = 'HTB-COM-002';
        $nombreReporte = 'Cotización de Proveedor';
        $datosHotel = HotelInfo::getBaseData();
        $barcodeBase64 = $this->barcodeGenerator->base64(
            code: (string) $cotizacion->id,
            height: 100,
            widthFactor: 4,
        );

        $layout = new LayoutPdf(
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );

        $pdf = Pdf::loadView('reports.compras.cotizaciones.cotizacion', [
            'record' => $cotizacionConRelaciones,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'barcodeBase64' => $barcodeBase64,
            'datosHotel' => $datosHotel,
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenSuperiorMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenSuperiorMm,
        ])->setPaper('letter');

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'cotizacion_id' => $cotizacion->id,
                'solicitud_id' => $cotizacion->solicitud_id,
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
