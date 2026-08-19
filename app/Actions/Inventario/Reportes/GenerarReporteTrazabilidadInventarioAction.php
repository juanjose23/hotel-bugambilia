<?php

declare(strict_types=1);

namespace App\Actions\Inventario\Reportes;

use App\Repository\Queries\Inventario\Trazabilidad\TrazabilidadLoteHaciaAdelante;
use App\Support\HotelInfo;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\Orientacion;
use App\Support\Pdf\TamanoPapel;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;

final readonly class GenerarReporteTrazabilidadInventarioAction
{
    public function __construct(
        private TrazabilidadLoteHaciaAdelante $trazabilidadLote,
    ) {}

    /** @param array<string, mixed> $params */
    public function pdf(array $params): DomPdfInstance
    {
        $loteId = is_numeric($params['lote_id'] ?? null) ? (int) $params['lote_id'] : 0;
        $trazabilidad = $this->trazabilidadLote->ejecutar($loteId);

        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        return Pdf::loadView('reports.inventario.trazabilidad.trazabilidad-lote', array_merge(HotelInfo::getBaseData(), [
            'nombreReporte' => 'Trazabilidad de Lote Hacia Adelante',
            'codigoReporte' => 'HTB-INV-011',
            'trazabilidad' => $trazabilidad,
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'pageSize' => $layout->tamano->cssName(),
            'orientation' => $layout->orientacion->cssName(),
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
            'pageContentHeight' => $layout->altoContenidoMm(),
            'pageContentWidth' => $layout->anchoContenidoMm(),
        ]))->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );
    }
}
