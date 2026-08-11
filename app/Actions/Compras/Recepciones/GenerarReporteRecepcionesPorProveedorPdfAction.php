<?php

declare(strict_types=1);

namespace App\Actions\Compras\Recepciones;

use App\BusinessLogic\Compras\Data\Reportes\RecepcionesPorProveedorReporteData;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;

final class GenerarReporteRecepcionesPorProveedorPdfAction
{
    use GuardaReporte;

    public function ejecutar(RecepcionesPorProveedorReporteData $reportData): PdfDocumento
    {
        $codigoReporte = 'HTB-COM-012';
        $nombreReporte = 'Recepciones por Proveedor';
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

        $pdf = Pdf::loadView('reports.compras.recepciones.recepciones-proveedor', [
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
        ])->setPaper('letter', 'portrait');

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
