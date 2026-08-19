<?php

declare(strict_types=1);

namespace App\Actions\Inventario\Reportes;

use App\Repository\Queries\Inventario\Gestion\ObtenerRotacionInventario;
use App\Repository\Queries\Inventario\Stock\ObtenerMovimientosInventario;
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

final readonly class GenerarReporteMovimientosInventarioAction
{
    public function __construct(
        private ObtenerMovimientosInventario $movimientosInventario,
        private ObtenerRotacionInventario $rotacion,
    ) {}

    /** @param array<string, mixed> $params */
    public function movimientosPdf(array $params): DomPdfInstance
    {
        $fechaInicio = is_string($params['fecha_inicio'] ?? null) ? $params['fecha_inicio'] : (is_string($params['fecha_desde'] ?? null) ? $params['fecha_desde'] : null);
        $fechaFin = is_string($params['fecha_fin'] ?? null) ? $params['fecha_fin'] : (is_string($params['fecha_hasta'] ?? null) ? $params['fecha_hasta'] : null);
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;

        $movimientosPaginator = $this->movimientosInventario->ejecutar([
            'fecha_desde' => $fechaInicio,
            'fecha_hasta' => $fechaFin,
            'producto_id' => $productoId,
        ], 500);

        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: collect($movimientosPaginator->items()),
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 20,
        );

        return Pdf::loadView('reports.inventario.movimientos.movimientos', array_merge(HotelInfo::getBaseData(), [
            'nombreReporte' => 'Movimientos de Inventario',
            'codigoReporte' => 'HTB-INV-002',
            'paginas' => $paginas,
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'fechaInicio' => $fechaInicio ?? 'Inicio',
            'fechaFin' => $fechaFin ?? 'Hoy',
            'totalMovimientos' => $movimientosPaginator->total(),
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
    public function rotacionPdf(array $params): DomPdfInstance
    {
        $meses = is_numeric($params['meses'] ?? null) ? (int) $params['meses'] : 3;
        $items = $this->rotacion->ejecutar(['meses' => $meses]);

        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: $items,
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 20,
        );

        return Pdf::loadView('reports.inventario.stock.rotacion', array_merge(HotelInfo::getBaseData(), [
            'nombreReporte' => 'Rotación de Inventario',
            'codigoReporte' => 'HTB-INV-008',
            'paginas' => $paginas,
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'meses' => $meses,
            'totalRegistros' => $items->count(),
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
    public function rotacionExcel(array $params): StreamedResponse
    {
        $meses = is_numeric($params['meses'] ?? null) ? (int) $params['meses'] : 3;
        $items = $this->rotacion->ejecutar(['meses' => $meses]);

        return (new GeneradorExcel)->descargar(
            coleccion: $items,
            nombre: 'HTB-INV-008-Rotacion-Inventario.xlsx',
            hoja: 'Rotación',
            columnas: [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                ColumnaExcel::make('Total Salidas', fn ($r) => (int) $r->totalSalidas, numerica: true),
                ColumnaExcel::make('Stock Promedio', fn ($r) => (float) $r->stockPromedio, numerica: true),
                ColumnaExcel::make('Índice Rotación', fn ($r) => (float) $r->indiceRotacion, numerica: true),
                ColumnaExcel::make('Clasificación', fn ($r) => $r->clasificacion),
            ],
        );
    }

    /** @param array<string, mixed> $params */
    public function movimientosExcel(array $params): StreamedResponse
    {
        $fechaInicio = is_string($params['fecha_inicio'] ?? null) ? $params['fecha_inicio'] : (is_string($params['fecha_desde'] ?? null) ? $params['fecha_desde'] : null);
        $fechaFin = is_string($params['fecha_fin'] ?? null) ? $params['fecha_fin'] : (is_string($params['fecha_hasta'] ?? null) ? $params['fecha_hasta'] : null);
        $productoId = is_numeric($params['producto_id'] ?? null) ? (int) $params['producto_id'] : null;

        $movimientosPaginator = $this->movimientosInventario->ejecutar([
            'fecha_desde' => $fechaInicio,
            'fecha_hasta' => $fechaFin,
            'producto_id' => $productoId,
        ], 5000);

        return (new GeneradorExcel)->descargar(
            coleccion: collect($movimientosPaginator->items()),
            nombre: 'HTB-INV-002-Movimientos-Inventario.xlsx',
            hoja: 'Movimientos',
            columnas: [
                ColumnaExcel::make('Fecha', fn ($r) => $r->created_at?->format('d/m/Y H:i') ?? 'N/A'),
                ColumnaExcel::make('Tipo', fn ($r) => $r->tipo?->label() ?? (is_string($r->tipo) ? $r->tipo : 'N/A')),
                ColumnaExcel::make('Producto', fn ($r) => $r->producto->nombre ?? 'N/A'),
                ColumnaExcel::make('Lote', fn ($r) => $r->lote->codigo_lote ?? 'N/A'),
                ColumnaExcel::make('Cantidad', fn ($r) => (float) ($r->cantidad ?? 0), numerica: true),
                ColumnaExcel::make('Origen', fn ($r) => $r->ubicacionOrigen->nombre ?? 'N/A'),
                ColumnaExcel::make('Destino', fn ($r) => $r->ubicacionDestino->nombre ?? 'N/A'),
                ColumnaExcel::make('Motivo', fn ($r) => $r->motivo ?? 'N/A'),
            ],
        );
    }
}
