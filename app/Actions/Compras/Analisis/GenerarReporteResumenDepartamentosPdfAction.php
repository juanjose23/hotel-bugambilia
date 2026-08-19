<?php

declare(strict_types=1);

namespace App\Actions\Compras\Analisis;

use App\Repository\Queries\Compras\Reportes\ObtenerResumenDepartamentosCompras;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\Orientacion;
use App\Support\Pdf\TamanoPapel;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;

final class GenerarReporteResumenDepartamentosPdfAction
{
    use GuardaReporte;

    public function __construct(
        private readonly ObtenerResumenDepartamentosCompras $query,
    ) {}

    /** @param array<string, mixed> $params */
    public function ejecutar(?string $fechaInicio = null, ?string $fechaFin = null, array $params = []): PdfDocumento
    {
        $codigoReporte = 'HTB-COM-017';
        $nombreReporte = 'Resumen de Compras por Departamento';
        $datosHotel = HotelInfo::getBaseData();

        $tamanoRaw = is_string($params['pageSize'] ?? null) ? $params['pageSize'] : (is_string($params['tamano'] ?? null) ? $params['tamano'] : 'letter');
        $orientacionRaw = is_string($params['orientation'] ?? null) ? $params['orientation'] : (is_string($params['orientacion'] ?? null) ? $params['orientacion'] : 'portrait');

        $layout = new LayoutPdf(
            tamano: TamanoPapel::fromRequest($tamanoRaw),
            orientacion: Orientacion::fromRequest($orientacionRaw),
        );

        $paginador = new ReportePaginador($layout);
        $resumenData = $this->query->ejecutar($fechaInicio, $fechaFin);
        $items = collect($resumenData->data);

        $paginas = $paginador->paginar(
            items: $items,
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 10,
        );

        $pdf = Pdf::loadView('reports.compras.analisis.resumen-departamentos', [
            'paginas' => $paginas,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'datosHotel' => $datosHotel,
            'fechaInicio' => $fechaInicio ?? 'Histórico',
            'fechaFin' => $fechaFin ?? 'Hoy',
            'totalGeneral' => $items->sum('total_oc'),
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
                'id' => 0,
                'codigo_referencia' => 'GENERAL',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'tamano' => $layout->tamano->cssName(),
                'orientacion' => $layout->orientacion->cssName(),
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
