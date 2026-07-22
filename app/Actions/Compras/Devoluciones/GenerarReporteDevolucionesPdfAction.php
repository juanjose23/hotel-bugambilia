<?php

declare(strict_types=1);

namespace App\Actions\Compras\Devoluciones;

use App\BusinessLogic\Compras\Data\Reportes\DevolucionesProveedorReporteData;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;

final class GenerarReporteDevolucionesPdfAction
{
    use GuardaReporte;

    public function ejecutar(DevolucionesProveedorReporteData $reportData): \Barryvdh\DomPDF\PDF
    {
        $codigoReporte = 'HTB-COM-016';
        $nombreReporte = 'Devoluciones y Reclamos por Proveedor';
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

        $pdf = Pdf::loadView('reports.compras.devoluciones.devoluciones-proveedor', [
            'paginas' => $paginas,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'datosHotel' => $datosHotel,
            'fechaInicio' => $reportData->fechaInicio,
            'fechaFin' => $reportData->fechaFin,
            'totalDevoluciones' => $reportData->totalDevoluciones,
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenSuperiorMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenSuperiorMm,
        ])->setPaper('letter');

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
