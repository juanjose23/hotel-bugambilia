<?php

declare(strict_types=1);

namespace App\Actions\Compras\Solicitudes;

use App\BusinessLogic\Compras\Data\Reportes\SolicitudesEstadoReporteData;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;

final class GenerarReporteSolicitudesEstadoPdfAction
{
    use GuardaReporte;

    public function ejecutar(SolicitudesEstadoReporteData $reportData): \Barryvdh\DomPDF\PDF
    {
        $codigoReporte = 'HTB-COM-010';
        $nombreReporte = 'Solicitudes de Compra por Estado';
        $datosHotel = HotelInfo::getBaseData();

        $layout = new LayoutPdf(
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );

        $paginador = new ReportePaginador($layout);
        $items = collect($reportData->data);

        $paginas = $paginador->paginar(
            items: $items,
            tipo: TiposReporte::TABLA_SIMPLE,
        );

        $pdf = Pdf::loadView('reports.compras.solicitudes.solicitudes-estado', [
            'paginas' => $paginas,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'datosHotel' => $datosHotel,
            'fechaInicio' => $reportData->fechaInicio,
            'fechaFin' => $reportData->fechaFin,
            'estado' => $reportData->estado,
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenSuperiorMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenSuperiorMm,
        ])->setPaper('letter', 'portrait');

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'fecha_inicio' => $reportData->fechaInicio,
                'fecha_fin' => $reportData->fechaFin,
                'estado' => $reportData->estado,
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
