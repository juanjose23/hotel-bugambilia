<?php

declare(strict_types=1);

namespace App\Actions\Compras\Solicitudes;

use App\Repository\Models\Compras\Solicitud;
use App\Repository\Queries\Compras\Solicitudes\ObtenerSolicitudReporteQuery;
use App\Support\Barcode\BarcodeGenerator;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use Barryvdh\DomPDF\Facade\Pdf;

final class GenerarReporteSolicitudPdfAction
{
    use GuardaReporte;

    public function __construct(
        private readonly BarcodeGenerator $barcodeGenerator,
        private readonly ObtenerSolicitudReporteQuery $query,
    ) {}

    public function ejecutar(Solicitud $solicitud): \Barryvdh\DomPDF\PDF
    {
        $solicitudConRelaciones = $this->query->ejecutar($solicitud->id) ?? $solicitud;

        $datosHotel = HotelInfo::getBaseData();
        $barcodeBase64 = $this->barcodeGenerator->base64(
            code: (string) $solicitud->id,
            height: 100,
            widthFactor: 4,
        );

        $codigoReporte = 'HTB-COM-001';
        $nombreReporte = 'Solicitud de Compra';

        $layout = new LayoutPdf(
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );

        $pdf = Pdf::loadView('reports.compras.solicitudes.solicitud-compra', [
            'solicitud' => $solicitudConRelaciones,
            'estadoLabel' => $solicitudConRelaciones->estado?->label() ?? '',
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
                'solicitud_id' => $solicitud->id,
                'codigo' => $solicitud->codigo,
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
