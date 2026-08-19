<?php

declare(strict_types=1);

namespace App\Actions\Activos\Reportes;

use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Repository\Queries\Activos\ObtenerReportesActivosVariosUseCase;
use App\Support\Excel\ColumnaExcel;
use App\Support\Excel\GeneradorExcel;
use App\Support\HotelInfo;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\Orientacion;
use App\Support\Pdf\TamanoPapel;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class GenerarReporteInventarioGeneralActivosAction
{
    public function __construct(
        private ObtenerReportesActivosVariosUseCase $reportesActivosVarios,
        private ConvertirMoneda $convertirMoneda,
    ) {}

    /** @param array<string, mixed> $params */
    public function pdf(array $params = []): DomPdfInstance
    {
        $filtros = [
            'estado' => $params['estado'] ?? null,
            'producto_id' => $params['producto_id'] ?? null,
            'ubicacion_tipo' => $params['ubicacion_tipo'] ?? null,
        ];
        $activos = $this->reportesActivosVarios->inventarioGeneral($filtros);

        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: $activos,
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 20,
        );

        $totalCosto = 0.0;
        foreach ($activos as $activo) {
            $totalCosto += $this->convertirMoneda->aBase((float) ($activo->costo_adquisicion ?? 0), $activo->moneda_id);
        }

        return Pdf::loadView('reports.activos.inventario-general', array_merge(HotelInfo::getBaseData(), [
            'nombreReporte' => 'Inventario General de Activos',
            'codigoReporte' => 'HTB-ACT-001',
            'paginas' => $paginas,
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'totalCosto' => $totalCosto,
            'totalRegistros' => $activos->count(),
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

    public function excel(): StreamedResponse
    {
        $activos = $this->reportesActivosVarios->inventarioGeneral([]);

        return (new GeneradorExcel)->descargar(
            coleccion: $activos,
            nombre: 'HTB-ACT-001-Inventario-General.xlsx',
            hoja: 'Activos',
            columnas: [
                ColumnaExcel::make('Código', fn ($r) => $r->codigo_inventario ?? $r->codigo),
                ColumnaExcel::make('Nombre', fn ($r) => $r->nombre),
                ColumnaExcel::make('Categoría', fn ($r) => $r->categoria->nombre ?? 'N/A'),
                ColumnaExcel::make('Ubicación', fn ($r) => $r->ubicacionTexto ?? 'N/A'),
                ColumnaExcel::make('Estado', fn ($r) => $r->estado?->label() ?? 'N/A'),
                ColumnaExcel::make('Costo Adquisición', fn ($r) => (float) $r->costo_adquisicion, numerica: true),
                ColumnaExcel::make('Fecha Adquisición', fn ($r) => $r->fecha_adquisicion?->format('d/m/Y') ?? 'N/A'),
            ],
        );
    }
}
