<?php

declare(strict_types=1);

namespace App\Actions\Inventario\Reportes;

use App\Repository\Queries\Inventario\Stock\ObtenerValorizacionInventario;
use App\Repository\Queries\Shared\ObtenerMonedaBase;
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

final readonly class GenerarReporteValorizacionInventarioAction
{
    public function __construct(
        private ObtenerValorizacionInventario $valorizacionInventario,
        private ObtenerMonedaBase $obtenerMonedaBase,
    ) {}

    /** @param array<string, mixed> $params */
    public function pdf(array $params): DomPdfInstance
    {
        $ubicacionId = is_numeric($params['ubicacion_id'] ?? null) ? (int) $params['ubicacion_id'] : null;
        $items = $this->valorizacionInventario->ejecutar(['ubicacion_id' => $ubicacionId]);
        $valorTotal = $this->valorizacionInventario->totalGeneral(['ubicacion_id' => $ubicacionId]);
        $moneda = $this->obtenerMonedaBase->ejecutar();

        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: $items,
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 20,
        );

        return Pdf::loadView('reports.inventario.stock.valorizacion', array_merge(HotelInfo::getBaseData(), [
            'nombreReporte' => 'Valorización de Almacén',
            'codigoReporte' => 'HTB-INV-007',
            'paginas' => $paginas,
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'valorTotal' => $valorTotal,
            'totalStock' => $items->sum('stockTotal'),
            'totalItems' => $items->count(),
            'monedaSimbolo' => $moneda !== null ? $moneda->simbolo : 'C$',
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
        $ubicacionId = is_numeric($params['ubicacion_id'] ?? null) ? (int) $params['ubicacion_id'] : null;
        $items = $this->valorizacionInventario->ejecutar(['ubicacion_id' => $ubicacionId]);

        return (new GeneradorExcel)->descargar(
            coleccion: $items,
            nombre: 'HTB-INV-007-Valorizacion-Almacen.xlsx',
            hoja: 'Valorización',
            columnas: [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                ColumnaExcel::make('Variante', fn ($r) => $r->variante ?? 'Sin Variante'),
                ColumnaExcel::make('Categoría', fn ($r) => $r->categoria ?? 'N/A'),
                ColumnaExcel::make('Ubicación', fn ($r) => $r->ubicacion ?? 'N/A'),
                ColumnaExcel::make('Stock Total', fn ($r) => (float) $r->stockTotal, numerica: true),
                ColumnaExcel::make('Costo Promedio', fn ($r) => (float) $r->costoPromedio, numerica: true),
                ColumnaExcel::make('Valor Total', fn ($r) => (float) $r->valorTotal, numerica: true),
            ],
        );
    }
}
