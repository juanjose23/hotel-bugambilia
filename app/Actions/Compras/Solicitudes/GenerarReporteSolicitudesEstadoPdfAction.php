<?php

declare(strict_types=1);

namespace App\Actions\Compras\Solicitudes;

use App\BusinessLogic\Compras\Data\Reportes\SolicitudesEstadoReporteData;
use App\Repository\Models\Compras\Solicitud;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\Orientacion;
use App\Support\Pdf\TamanoPapel;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;
use Illuminate\Support\Collection;

final class GenerarReporteSolicitudesEstadoPdfAction
{
    use GuardaReporte;

    /** @param array<string, mixed> $params */
    public function ejecutar(SolicitudesEstadoReporteData $reportData, array $params = []): PdfDocumento
    {
        $codigoReporte = 'HTB-COM-010';
        $nombreReporte = 'Solicitudes de Compra por Estado';

        $tamanoRaw = is_string($params['pageSize'] ?? null) ? $params['pageSize'] : (is_string($params['tamano'] ?? null) ? $params['tamano'] : 'letter');
        $orientacionRaw = is_string($params['orientation'] ?? null) ? $params['orientation'] : (is_string($params['orientacion'] ?? null) ? $params['orientacion'] : 'portrait');

        $layout = new LayoutPdf(
            tamano: TamanoPapel::fromRequest($tamanoRaw),
            orientacion: Orientacion::fromRequest($orientacionRaw),
        );
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
                'formato_pagina' => $params['formato_pagina'] ?? null,
                'pageSize' => $layout->tamano->cssName(),
                'orientation' => $layout->orientacion->cssName(),
            ],
        ))->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'fecha_inicio' => $reportData->fechaInicio,
                'fecha_fin' => $reportData->fechaFin,
                'estado' => $reportData->estado,
                'tamano' => $layout->tamano->cssName(),
                'orientacion' => $layout->orientacion->cssName(),
            ],
            pdf: $pdf,
        );

        return $pdf;
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
            altoExtraPrimeraPaginaMm: 10,
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
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
            'pageContentHeight' => $layout->altoContenidoMm(),
            'pageContentWidth' => $layout->anchoContenidoMm(),
        ], $extra);
    }
}
