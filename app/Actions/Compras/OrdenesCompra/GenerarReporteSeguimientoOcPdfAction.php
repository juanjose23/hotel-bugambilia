<?php

declare(strict_types=1);

namespace App\Actions\Compras\OrdenesCompra;

use App\BusinessLogic\Compras\Data\Reportes\SeguimientoOrdenCompraReporteData;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\Orientacion;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;

final class GenerarReporteSeguimientoOcPdfAction
{
    use GuardaReporte;

    public function ejecutar(SeguimientoOrdenCompraReporteData $reportData): \Barryvdh\DomPDF\PDF
    {
        $codigoReporte = 'HTB-COM-011';
        $nombreReporte = 'Seguimiento de Órdenes de Compra';
        $datosHotel = HotelInfo::getBaseData();

        $layout = new LayoutPdf(
            orientacion: Orientacion::Horizontal,
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

        $pdf = Pdf::loadView('reports.compras.ordenes_compra.seguimiento-oc', [
            'paginas' => $paginas,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'datosHotel' => $datosHotel,
            'fechaInicio' => $reportData->fechaInicio,
            'fechaFin' => $reportData->fechaFin,
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenSuperiorMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenSuperiorMm,
        ])->setPaper('letter', 'landscape');

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'fecha_inicio' => $reportData->fechaInicio,
                'fecha_fin' => $reportData->fechaFin,
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
