<?php

declare(strict_types=1);

namespace App\Actions\Inventario\Reportes;

use App\Repository\Queries\Inventario\Alertas\ObtenerLotesCuarentena;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesProximosVencer;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesVencidos;
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

final readonly class GenerarReporteAlertasInventarioAction
{
    public function __construct(
        private ObtenerLotesCuarentena $lotesCuarentena,
        private ObtenerLotesProximosVencer $lotesProximosVencer,
        private ObtenerLotesVencidos $lotesVencidos,
    ) {}

    /** @param array<string, mixed> $params */
    public function cuarentenaPdf(array $params): DomPdfInstance
    {
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;
        $lotes = $this->lotesCuarentena->ejecutar(['producto_id' => $productoId]);

        return $this->generarPdf('reports.inventario.lotes.cuarentena', [
            'nombreReporte' => 'Productos en Cuarentena',
            'codigoReporte' => 'HTB-INV-004',
            'totalLotes' => $lotes->count(),
        ], collect($lotes->all()), $params);
    }

    /** @param array<string, mixed> $params */
    public function cuarentenaExcel(array $params): StreamedResponse
    {
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;
        $lotes = $this->lotesCuarentena->ejecutar(['producto_id' => $productoId]);

        return (new GeneradorExcel)->descargar(
            coleccion: $lotes,
            nombre: 'HTB-INV-004-Productos-Cuarentena.xlsx',
            hoja: 'Cuarentena',
            columnas: [
                ColumnaExcel::make('Lote', fn ($r) => $r->numero_lote ?? $r->codigo),
                ColumnaExcel::make('Producto', fn ($r) => $r->producto->nombre ?? 'N/A'),
                ColumnaExcel::make('Cantidad', fn ($r) => (float) ($r->cantidad_actual ?? $r->cantidad ?? 0), numerica: true),
                ColumnaExcel::make('Fecha Ingreso', fn ($r) => $r->fecha_entrada?->format('d/m/Y') ?? 'N/A'),
                ColumnaExcel::make('Motivo Retención', fn ($r) => $r->motivo_cuarentena ?? 'Control de Calidad'),
            ],
        );
    }

    /** @param array<string, mixed> $params */
    public function proximosVencerPdf(array $params): DomPdfInstance
    {
        $dias = is_numeric($params['dias'] ?? null) ? (int) $params['dias'] : 30;
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;
        $lotes = $this->lotesProximosVencer->ejecutar(['dias' => $dias, 'producto_id' => $productoId]);

        return $this->generarPdf('reports.inventario.lotes.proximos-vencer', [
            'nombreReporte' => 'Próximos Vencimientos de Inventario',
            'codigoReporte' => 'HTB-INV-005',
            'dias' => $dias,
            'totalLotes' => $lotes->count(),
        ], collect($lotes->all()), $params);
    }

    /** @param array<string, mixed> $params */
    public function proximosVencerExcel(array $params): StreamedResponse
    {
        $dias = is_numeric($params['dias'] ?? null) ? (int) $params['dias'] : 30;
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;
        $lotes = $this->lotesProximosVencer->ejecutar(['dias' => $dias, 'producto_id' => $productoId]);

        return (new GeneradorExcel)->descargar(
            coleccion: $lotes,
            nombre: 'HTB-INV-005-Proximos-Vencimientos.xlsx',
            hoja: 'Próximos Vencimientos',
            columnas: [
                ColumnaExcel::make('Lote', fn ($r) => $r->numero_lote ?? $r->codigo),
                ColumnaExcel::make('Producto', fn ($r) => $r->producto->nombre ?? 'N/A'),
                ColumnaExcel::make('Cantidad', fn ($r) => (float) ($r->cantidad_actual ?? $r->cantidad ?? 0), numerica: true),
                ColumnaExcel::make('Fecha Vencimiento', fn ($r) => $r->fecha_vencimiento?->format('d/m/Y') ?? 'N/A'),
                ColumnaExcel::make('Días Restantes', fn ($r) => (int) ($r->dias_restantes ?? 0), numerica: true),
            ],
        );
    }

    /** @param array<string, mixed> $params */
    public function vencidosPdf(array $params): DomPdfInstance
    {
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;
        $lotes = $this->lotesVencidos->ejecutar(['producto_id' => $productoId]);

        return $this->generarPdf('reports.inventario.lotes.vencidos', [
            'nombreReporte' => 'Productos Vencidos',
            'codigoReporte' => 'HTB-INV-012',
            'totalLotes' => $lotes->count(),
        ], collect($lotes->all()), $params);
    }

    /** @param array<string, mixed> $params */
    public function vencidosExcel(array $params): StreamedResponse
    {
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;
        $lotes = $this->lotesVencidos->ejecutar(['producto_id' => $productoId]);

        return (new GeneradorExcel)->descargar(
            coleccion: $lotes,
            nombre: 'HTB-INV-012-Productos-Vencidos.xlsx',
            hoja: 'Vencidos',
            columnas: [
                ColumnaExcel::make('Lote', fn ($r) => $r->numero_lote ?? $r->codigo),
                ColumnaExcel::make('Producto', fn ($r) => $r->producto->nombre ?? 'N/A'),
                ColumnaExcel::make('Cantidad Vencida', fn ($r) => (float) ($r->cantidad_actual ?? $r->cantidad ?? 0), numerica: true),
                ColumnaExcel::make('Fecha Vencimiento', fn ($r) => $r->fecha_vencimiento?->format('d/m/Y') ?? 'N/A'),
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
