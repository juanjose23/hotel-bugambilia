<?php

declare(strict_types=1);

namespace App\Actions\Inventario\Reportes;

use App\Repository\Queries\Inventario\Stock\ObtenerStockMinimo;
use App\Repository\Queries\Inventario\Stock\ObtenerStockPorProducto;
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

final readonly class GenerarReporteStockInventarioAction
{
    public function __construct(
        private ObtenerStockPorProducto $stockPorProducto,
        private ObtenerStockMinimo $obtenerStockMinimo,
    ) {}

    /** @param array<string, mixed> $params */
    public function stockPdf(array $params): DomPdfInstance
    {
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;
        $ubicacionId = is_numeric($params['ubicacion_id'] ?? null) ? (int) $params['ubicacion_id'] : null;
        $stock = $this->stockPorProducto->ejecutar([
            'producto_id' => $productoId,
            'ubicacion_id' => $ubicacionId,
        ]);

        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: $stock,
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 20,
        );

        return Pdf::loadView('reports.inventario.stock.stock-por-producto', array_merge(HotelInfo::getBaseData(), [
            'nombreReporte' => 'Inventario de Productos',
            'codigoReporte' => 'HTB-INV-001',
            'paginas' => $paginas,
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'totalStock' => $stock->sum('stockDisponible'),
            'totalCuarentena' => $stock->sum('stockCuarentena'),
            'totalRegistros' => $stock->count(),
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

    /** @param array<string, mixed> $params */
    public function stockExcel(array $params): StreamedResponse
    {
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;
        $ubicacionId = is_numeric($params['ubicacion_id'] ?? null) ? (int) $params['ubicacion_id'] : null;
        $stock = $this->stockPorProducto->ejecutar([
            'producto_id' => $productoId,
            'ubicacion_id' => $ubicacionId,
        ]);

        return (new GeneradorExcel)->descargar(
            coleccion: $stock,
            nombre: 'HTB-INV-001-Stock-Productos.xlsx',
            hoja: 'Stock',
            columnas: [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                ColumnaExcel::make('Variante', fn ($r) => $r->variante ?? 'Sin Variante'),
                ColumnaExcel::make('Categoría', fn ($r) => $r->categoria ?? 'N/A'),
                ColumnaExcel::make('Ubicación', fn ($r) => $r->ubicacion ?? 'N/A'),
                ColumnaExcel::make('Stock Disponible', fn ($r) => (float) $r->stockDisponible, numerica: true),
                ColumnaExcel::make('Stock Cuarentena', fn ($r) => (float) $r->stockCuarentena, numerica: true),
                ColumnaExcel::make('Total Lotes', fn ($r) => (int) $r->totalLotes, numerica: true),
            ],
        );
    }

    /** @param array<string, mixed> $params */
    public function stockMinimoPdf(array $params): DomPdfInstance
    {
        $categoriaId = is_numeric($params['categoria_id'] ?? null) ? (int) $params['categoria_id'] : null;
        $stock = $this->obtenerStockMinimo->ejecutar([
            'categoria_id' => $categoriaId,
        ]);

        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: $stock,
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 20,
        );

        return Pdf::loadView('reports.inventario.stock.stock-minimo', array_merge(HotelInfo::getBaseData(), [
            'nombreReporte' => 'Stock Mínimo y Punto de Pedido',
            'codigoReporte' => 'HTB-INV-009',
            'paginas' => $paginas,
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'totalCriticos' => $stock->count(),
            'totalStockActual' => $stock->sum('stockActual'),
            'totalPendiente' => $stock->sum('pendienteReplenish'),
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

    /** @param array<string, mixed> $params */
    public function stockMinimoExcel(array $params): StreamedResponse
    {
        $categoriaId = is_numeric($params['categoria_id'] ?? null) ? (int) $params['categoria_id'] : null;
        $stock = $this->obtenerStockMinimo->ejecutar([
            'categoria_id' => $categoriaId,
        ]);

        return (new GeneradorExcel)->descargar(
            coleccion: $stock,
            nombre: 'HTB-INV-009-Stock-Minimo.xlsx',
            hoja: 'Stock Mínimo',
            columnas: [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                ColumnaExcel::make('Variante', fn ($r) => $r->variante ?? 'Sin Variante'),
                ColumnaExcel::make('Categoría', fn ($r) => $r->categoria ?? 'N/A'),
                ColumnaExcel::make('Stock Actual', fn ($r) => (float) $r->stockActual, numerica: true),
                ColumnaExcel::make('Punto Pedido', fn ($r) => (float) $r->puntoPedido, numerica: true),
                ColumnaExcel::make('Pendiente Reposición', fn ($r) => (float) $r->pendienteReplenish, numerica: true),
                ColumnaExcel::make('Estado', fn ($r) => $r->estado),
            ],
        );
    }
}
