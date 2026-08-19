<?php

declare(strict_types=1);

namespace App\Actions\Inventario\Reportes;

use App\Repository\Queries\Inventario\Stock\ObtenerAnalisisCostoVentas;
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

final readonly class GenerarReporteCostoVentasInventarioAction
{
    public function __construct(
        private ObtenerAnalisisCostoVentas $obtenerAnalisisCostoVentas,
    ) {}

    /** @param array<string, mixed> $params */
    public function pdf(array $params): DomPdfInstance
    {
        $fechaInicio = is_string($params['fecha_inicio'] ?? null) ? $params['fecha_inicio'] : (is_string($params['fecha_desde'] ?? null) ? $params['fecha_desde'] : null);
        $fechaFin = is_string($params['fecha_fin'] ?? null) ? $params['fecha_fin'] : (is_string($params['fecha_hasta'] ?? null) ? $params['fecha_hasta'] : null);

        $items = $this->obtenerAnalisisCostoVentas->ejecutar([
            'fecha_desde' => $fechaInicio,
            'fecha_hasta' => $fechaFin,
        ]);

        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'landscape');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: $items,
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 20,
        );

        return Pdf::loadView('reports.inventario.stock.costo-ventas', array_merge(HotelInfo::getBaseData(), [
            'nombreReporte' => 'Análisis de Costo de Ventas',
            'codigoReporte' => 'HTB-INV-013',
            'paginas' => $paginas,
            'fechaInicio' => $fechaInicio ?? 'Inicio',
            'fechaFin' => $fechaFin ?? 'Hoy',
            'filtros' => [
                'fecha_desde' => $fechaInicio,
                'fecha_hasta' => $fechaFin,
            ],
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

    /** @param array<string, mixed> $params */
    public function excel(array $params): StreamedResponse
    {
        $fechaInicio = is_string($params['fecha_inicio'] ?? null) ? $params['fecha_inicio'] : (is_string($params['fecha_desde'] ?? null) ? $params['fecha_desde'] : null);
        $fechaFin = is_string($params['fecha_fin'] ?? null) ? $params['fecha_fin'] : (is_string($params['fecha_hasta'] ?? null) ? $params['fecha_hasta'] : null);

        $items = $this->obtenerAnalisisCostoVentas->ejecutar([
            'fecha_desde' => $fechaInicio,
            'fecha_hasta' => $fechaFin,
        ]);

        return (new GeneradorExcel)->descargar(
            coleccion: $items,
            nombre: 'HTB-INV-013-Costo-Ventas.xlsx',
            hoja: 'Costo de Ventas',
            columnas: [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                ColumnaExcel::make('Variante', fn ($r) => $r->variante ?? 'Sin Variante'),
                ColumnaExcel::make('Categoría', fn ($r) => $r->categoria ?? 'N/A'),
                ColumnaExcel::make('Cant. Comprada', fn ($r) => (float) $r->cantidadComprada, numerica: true),
                ColumnaExcel::make('Costo Compras', fn ($r) => (float) $r->costoCompras, numerica: true),
                ColumnaExcel::make('Cant. Consumida', fn ($r) => (float) $r->cantidadConsumida, numerica: true),
                ColumnaExcel::make('Costo Consumo', fn ($r) => (float) $r->costoConsumo, numerica: true),
                ColumnaExcel::make('Desviación %', fn ($r) => (float) $r->desviacionPorcentaje, numerica: true),
            ],
        );
    }
}
