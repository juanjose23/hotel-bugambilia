<?php

declare(strict_types=1);

namespace App\Actions\Compras\Analisis;

use App\BusinessLogic\Compras\Data\Reportes\TrazabilidadCompletaReporteData;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use Barryvdh\DomPDF\Facade\Pdf;

final class GenerarReporteTrazabilidadCompletaPdfAction
{
    use GuardaReporte;

    public function ejecutar(TrazabilidadCompletaReporteData $reportData): \Barryvdh\DomPDF\PDF
    {
        $codigoReporte = 'HTB-COM-009';
        $nombreReporte = 'Trazabilidad Completa del Proceso de Compra';
        $datosHotel = HotelInfo::getBaseData();

        $layout = new LayoutPdf(
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );

        $pdf = Pdf::loadView('reports.compras.analisis.trazabilidad-completa', [
            'solicitud' => $reportData->solicitud,
            'cotizaciones' => $reportData->cotizaciones,
            'ordenesCompra' => $reportData->ordenesCompra,
            'recepciones' => $reportData->recepciones,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'datosHotel' => $datosHotel,
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenSuperiorMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenSuperiorMm,
        ])->setPaper('letter', 'portrait');

        $solicitudCodigo = isset($reportData->solicitud->codigo)
            ? $reportData->solicitud->codigo
            : 'N/A';

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: ['solicitud_codigo' => $solicitudCodigo],
            pdf: $pdf,
        );

        return $pdf;
    }
}
