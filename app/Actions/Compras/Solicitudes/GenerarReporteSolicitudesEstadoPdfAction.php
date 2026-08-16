<?php

declare(strict_types=1);

namespace App\Actions\Compras\Solicitudes;

use App\BusinessLogic\Compras\Data\Reportes\SolicitudesEstadoReporteData;
use App\Repository\Models\Compras\Solicitud;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;
use Illuminate\Support\Collection;

final class GenerarReporteSolicitudesEstadoPdfAction
{
    use GuardaReporte;

    public function ejecutar(SolicitudesEstadoReporteData $reportData): PdfDocumento
    {
        $codigoReporte = 'HTB-COM-010';
        $nombreReporte = 'Solicitudes de Compra por Estado';
        $layout = $this->construirLayout();
        $paginas = $this->paginarDatos($reportData->data, $layout);

        $pdf = Pdf::loadView('reports.compras.solicitudes.solicitudes-estado', $this->parametrosVista(
            codigoReporte: $codigoReporte,
            nombreReporte: $nombreReporte,
            layout: $layout,
            extra: [
                'paginas' => $paginas,
                'fechaInicio' => $reportData->fechaInicio,
                'fechaFin' => $reportData->fechaFin,
                'estado' => $reportData->estado,
            ],
        ))->setPaper('letter', 'portrait');

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

    private function construirLayout(): LayoutPdf
    {
        return new LayoutPdf(
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );
    }

    /**
     * @param  array<int, Solicitud>  $data
     * @return array<int, Collection<int, Solicitud>>
     */
    private function paginarDatos(array $data, LayoutPdf $layout): array
    {
        $paginador = new ReportePaginador($layout);

        return $paginador->paginar(
            items: collect($data),
            tipo: TiposReporte::TABLA_SIMPLE,
        );
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function parametrosVista(string $codigoReporte, string $nombreReporte, LayoutPdf $layout, array $extra = []): array
    {
        return array_merge([
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'datosHotel' => HotelInfo::getBaseData(),
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenSuperiorMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenSuperiorMm,
        ], $extra);
    }
}
