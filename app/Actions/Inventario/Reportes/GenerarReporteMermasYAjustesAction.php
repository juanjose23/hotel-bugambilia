<?php

declare(strict_types=1);

namespace App\Actions\Inventario\Reportes;

use App\Repository\Queries\Inventario\Mermas\ObtenerLotesMerma;
use App\Repository\Queries\Inventario\Mermas\ObtenerMermasTotales;
use App\Repository\Queries\Inventario\Stock\ObtenerAjustesInventario;
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
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class GenerarReporteMermasYAjustesAction
{
    public function __construct(
        private ObtenerLotesMerma $lotesMerma,
        private ObtenerMermasTotales $mermasTotales,
        private ObtenerAjustesInventario $obtenerAjustesInventario,
    ) {}

    /** @param array<string, mixed> $params */
    public function mermasPdf(array $params): DomPdfInstance
    {
        $fechaInicio = is_string($params['fecha_inicio'] ?? null) ? $params['fecha_inicio'] : (is_string($params['fecha_desde'] ?? null) ? $params['fecha_desde'] : null);
        $fechaFin = is_string($params['fecha_fin'] ?? null) ? $params['fecha_fin'] : (is_string($params['fecha_hasta'] ?? null) ? $params['fecha_hasta'] : null);

        $filtros = [
            'periodo_desde' => $fechaInicio,
            'periodo_hasta' => $fechaFin,
        ];
        $mermas = $this->lotesMerma->ejecutar($filtros);
        $totales = $this->mermasTotales->ejecutar($filtros);

        return $this->generarPdf('reports.inventario.mermas.mermas', [
            'nombreReporte' => 'Mermas y Pérdidas de Inventario',
            'codigoReporte' => 'HTB-INV-006',
            'fechaInicio' => $fechaInicio ?? 'Inicio',
            'fechaFin' => $fechaFin ?? 'Hoy',
            'totalPerdida' => $totales['costo_total'] ?? $mermas->sum('costo_total'),
            'totalRegistros' => $mermas->count(),
        ], collect($mermas->all()), $params);
    }

    /** @param array<string, mixed> $params */
    public function mermasExcel(array $params): StreamedResponse
    {
        $fechaInicio = is_string($params['fecha_inicio'] ?? null) ? $params['fecha_inicio'] : (is_string($params['fecha_desde'] ?? null) ? $params['fecha_desde'] : null);
        $fechaFin = is_string($params['fecha_fin'] ?? null) ? $params['fecha_fin'] : (is_string($params['fecha_hasta'] ?? null) ? $params['fecha_hasta'] : null);

        $mermas = $this->lotesMerma->ejecutar([
            'periodo_desde' => $fechaInicio,
            'periodo_hasta' => $fechaFin,
        ]);

        return (new GeneradorExcel)->descargar(
            coleccion: $mermas,
            nombre: 'HTB-INV-006-Mermas-Inventario.xlsx',
            hoja: 'Mermas',
            columnas: [
                ColumnaExcel::make('Lote', fn ($r) => $r->numero_lote ?? $r->codigo),
                ColumnaExcel::make('Producto', fn ($r) => $r->producto->nombre ?? 'N/A'),
                ColumnaExcel::make('Cantidad Merma', fn ($r) => (float) ($r->cantidad ?? 0), numerica: true),
                ColumnaExcel::make('Tipo Merma', fn ($r) => $r->tipo_merma?->label() ?? 'N/A'),
                ColumnaExcel::make('Costo Total', fn ($r) => (float) ($r->costo_total ?? 0), numerica: true),
                ColumnaExcel::make('Fecha', fn ($r) => $r->created_at?->format('d/m/Y') ?? 'N/A'),
            ],
        );
    }

    /** @param array<string, mixed> $params */
    public function ajustesPdf(array $params): DomPdfInstance
    {
        $fechaInicio = is_string($params['fecha_inicio'] ?? null) ? $params['fecha_inicio'] : (is_string($params['fecha_desde'] ?? null) ? $params['fecha_desde'] : null);
        $fechaFin = is_string($params['fecha_fin'] ?? null) ? $params['fecha_fin'] : (is_string($params['fecha_hasta'] ?? null) ? $params['fecha_hasta'] : null);
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;

        $ajustesPaginator = $this->obtenerAjustesInventario->ejecutar([
            'fecha_desde' => $fechaInicio,
            'fecha_hasta' => $fechaFin,
            'producto_id' => $productoId,
        ], 500);

        return $this->generarPdf('reports.inventario.movimientos.ajustes', [
            'nombreReporte' => 'Ajustes de Inventario',
            'codigoReporte' => 'HTB-INV-010',
            'fechaInicio' => $fechaInicio ?? 'Inicio',
            'fechaFin' => $fechaFin ?? 'Hoy',
            'totalAjustes' => $ajustesPaginator->total(),
        ], collect($ajustesPaginator->items()), $params);
    }

    /** @param array<string, mixed> $params */
    public function ajustesExcel(array $params): StreamedResponse
    {
        $fechaInicio = is_string($params['fecha_inicio'] ?? null) ? $params['fecha_inicio'] : (is_string($params['fecha_desde'] ?? null) ? $params['fecha_desde'] : null);
        $fechaFin = is_string($params['fecha_fin'] ?? null) ? $params['fecha_fin'] : (is_string($params['fecha_hasta'] ?? null) ? $params['fecha_hasta'] : null);
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;

        $ajustesPaginator = $this->obtenerAjustesInventario->ejecutar([
            'fecha_desde' => $fechaInicio,
            'fecha_hasta' => $fechaFin,
            'producto_id' => $productoId,
        ], 5000);

        return (new GeneradorExcel)->descargar(
            coleccion: collect($ajustesPaginator->items()),
            nombre: 'HTB-INV-010-Ajustes-Inventario.xlsx',
            hoja: 'Ajustes',
            columnas: [
                ColumnaExcel::make('Código', fn ($r) => $r->codigo_ajuste ?? $r->codigo),
                ColumnaExcel::make('Producto', fn ($r) => $r->producto->nombre ?? 'N/A'),
                ColumnaExcel::make('Tipo Ajuste', fn ($r) => $r->tipo_ajuste?->label() ?? 'N/A'),
                ColumnaExcel::make('Cantidad', fn ($r) => (float) ($r->cantidad ?? 0), numerica: true),
                ColumnaExcel::make('Motivo', fn ($r) => $r->motivo ?? 'N/A'),
                ColumnaExcel::make('Fecha', fn ($r) => $r->created_at?->format('d/m/Y') ?? 'N/A'),
            ],
        );
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  array<string, mixed>  $viewData
     * @param  Collection<TKey, TValue>  $items
     * @param  array<string, mixed>  $params
     */
    private function generarPdf(string $view, array $viewData, Collection $items, array $params): DomPdfInstance
    {
        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: $items,
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 20,
        );

        return Pdf::loadView($view, array_merge(HotelInfo::getBaseData(), $viewData, [
            'paginas' => $paginas,
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
