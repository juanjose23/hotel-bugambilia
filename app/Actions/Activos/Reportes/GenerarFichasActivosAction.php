<?php

declare(strict_types=1);

namespace App\Actions\Activos\Reportes;

use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Queries\Activos\ObtenerFichasReportesUseCase;
use App\Support\HotelInfo;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\Orientacion;
use App\Support\Pdf\TamanoPapel;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;

final readonly class GenerarFichasActivosAction
{
    public function __construct(
        private ObtenerFichasReportesUseCase $fichasReportes,
    ) {}

    /** @param array<string, mixed> $params */
    public function fichaActivo(array $params): DomPdfInstance
    {
        /** @var Activo $activo */
        $activo = $params['activo'];
        $record = $this->fichasReportes->fichaActivo($activo);

        return $this->generarPdf('reports.activos.activo', [
            'nombreReporte' => 'Ficha de Activo',
            'codigoReporte' => 'HTB-ACT-002',
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'record' => $record,
        ], $params);
    }

    /** @param array<string, mixed> $params */
    public function fichaMantenimiento(array $params): DomPdfInstance
    {
        /** @var ActivoMantenimiento $mantenimiento */
        $mantenimiento = $params['mantenimiento'];
        $record = $this->fichasReportes->fichaMantenimiento($mantenimiento);

        return $this->generarPdf('reports.activos.mantenimiento', [
            'nombreReporte' => 'Ficha de Mantenimiento',
            'codigoReporte' => 'HTB-ACT-003',
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'record' => $record,
        ], $params);
    }

    /**
     * @param  array<string, mixed>  $viewData
     * @param  array<string, mixed>  $params
     */
    private function generarPdf(string $view, array $viewData, array $params): DomPdfInstance
    {
        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        return Pdf::loadView($view, array_merge(HotelInfo::getBaseData(), $viewData, [
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
