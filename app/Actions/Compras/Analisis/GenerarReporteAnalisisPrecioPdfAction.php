<?php

declare(strict_types=1);

namespace App\Actions\Compras\Analisis;

use App\BusinessLogic\Compras\Data\Reportes\AnalisisPrecioHistoricoReporteData;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\Orientacion;
use App\Support\Pdf\TamanoPapel;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;

final class GenerarReporteAnalisisPrecioPdfAction
{
    use GuardaReporte;

    /** @param array<string, mixed> $params */
    public function ejecutar(AnalisisPrecioHistoricoReporteData $reportData, array $params = []): PdfDocumento
    {
        $codigoReporte = 'HTB-COM-013';
        $nombreReporte = 'Análisis de Precios Histórico';
        $datosHotel = HotelInfo::getBaseData();

        $tamanoRaw = is_string($params['pageSize'] ?? null) ? $params['pageSize'] : (is_string($params['tamano'] ?? null) ? $params['tamano'] : 'letter');
        $orientacionRaw = is_string($params['orientation'] ?? null) ? $params['orientation'] : (is_string($params['orientacion'] ?? null) ? $params['orientacion'] : 'portrait');

        $layout = new LayoutPdf(
            tamano: TamanoPapel::fromRequest($tamanoRaw),
            orientacion: Orientacion::fromRequest($orientacionRaw),
        );

        $paginador = new ReportePaginador($layout);
        $items = collect($reportData->data);

        $paginas = $paginador->paginar(
            items: $items,
            tipo: TiposReporte::TABLA_MAESTRO_DETALLE,
            altoExtraPrimeraPaginaMm: 0,
        );

        $pdf = Pdf::loadView('reports.compras.analisis.analisis-precio', [
            'paginas' => $paginas,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'datosHotel' => $datosHotel,
            'fechaInicio' => $reportData->fechaInicio,
            'fechaFin' => $reportData->fechaFin,
            'meses' => $reportData->meses,
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'pageSize' => $layout->tamano->cssName(),
            'orientation' => $layout->orientacion->cssName(),
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
            'pageContentHeight' => $layout->altoContenidoMm(),
            'pageContentWidth' => $layout->anchoContenidoMm(),
        ])->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'fecha_inicio' => $reportData->fechaInicio,
                'fecha_fin' => $reportData->fechaFin,
                'meses' => $reportData->meses,
                'tamano' => $layout->tamano->cssName(),
                'orientacion' => $layout->orientacion->cssName(),
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
