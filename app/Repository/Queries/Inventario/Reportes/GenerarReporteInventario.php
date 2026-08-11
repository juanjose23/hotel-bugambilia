<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Reportes;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesCuarentena;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesProximosVencer;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesVencidos;
use App\Repository\Queries\Inventario\Gestion\ObtenerRotacionInventario;
use App\Repository\Queries\Inventario\Mermas\ObtenerLotesMerma;
use App\Repository\Queries\Inventario\Mermas\ObtenerMermasTotales;
use App\Repository\Queries\Inventario\Stock\ObtenerAjustesInventario;
use App\Repository\Queries\Inventario\Stock\ObtenerAnalisisCostoVentas;
use App\Repository\Queries\Inventario\Stock\ObtenerMovimientosInventario;
use App\Repository\Queries\Inventario\Stock\ObtenerStockMinimo;
use App\Repository\Queries\Inventario\Stock\ObtenerStockPorProducto;
use App\Repository\Queries\Inventario\Stock\ObtenerValorizacionInventario;
use App\Repository\Queries\Inventario\Trazabilidad\TrazabilidadLoteHaciaAdelante;
use App\Repository\Queries\Shared\ObtenerMonedaBase;
use App\Support\Excel\ColumnaExcel;
use App\Support\Excel\GeneradorExcel;
use App\Support\HotelInfo;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GenerarReporteInventario
{
    public function __construct(
        private readonly RegistrarAuditoriaReporte $auditoria,
        private readonly ObtenerStockPorProducto $stockPorProducto,
        private readonly ObtenerMovimientosInventario $movimientosInventario,
        private readonly ObtenerLotesCuarentena $lotesCuarentena,
        private readonly ObtenerLotesProximosVencer $lotesProximosVencer,
        private readonly ObtenerLotesMerma $lotesMerma,
        private readonly ObtenerMermasTotales $mermasTotales,
        private readonly ObtenerRotacionInventario $rotacion,
        private readonly ObtenerValorizacionInventario $valorizacionInventario,
        private readonly TrazabilidadLoteHaciaAdelante $trazabilidadLote,
        private readonly ObtenerLotesVencidos $lotesVencidos,
        private readonly ObtenerStockMinimo $obtenerStockMinimo,
        private readonly ObtenerAjustesInventario $obtenerAjustesInventario,
        private readonly ObtenerAnalisisCostoVentas $obtenerAnalisisCostoVentas,
        private readonly ObtenerMonedaBase $obtenerMonedaBase,
        private readonly ReportePaginador $reportePaginador,
    ) {}

    /** @param  array<string, mixed>  $params */
    public function execute(string $reportName, array $params = []): DomPdfInstance
    {
        return match ($reportName) {
            'stockPorProductoPdf' => $this->stockPorProductoPdf($params),
            'movimientosPdf' => $this->movimientosPdf($params),
            'cuarentenaPdf' => $this->cuarentenaPdf($params),
            'proximosVencerPdf' => $this->proximosVencerPdf($params),
            'mermasPdf' => $this->mermasPdf($params),
            'valorizacionPdf' => $this->valorizacionPdf($params),
            'rotacionPdf' => $this->rotacionPdf($params),
            'trazabilidadLotePdf' => $this->trazabilidadLotePdf($params),
            'vencidosPdf' => $this->vencidosPdf($params),
            'stockMinimoPdf' => $this->stockMinimoPdf($params),
            'ajustesPdf' => $this->ajustesPdf($params),
            'costoVentasPdf' => $this->costoVentasPdf($params),
            default => throw new InvalidArgumentException("Reporte '{$reportName}' no soportado."),
        };
    }

    /** @param  array<string, mixed>  $params */
    public function executeExcel(string $reportName, array $params = []): StreamedResponse
    {
        return match ($reportName) {
            'stockPorProductoExcel' => $this->stockPorProductoExcel($params),
            'movimientosExcel' => $this->movimientosExcel($params),
            'cuarentenaExcel' => $this->cuarentenaExcel($params),
            'proximosVencerExcel' => $this->proximosVencerExcel($params),
            'valorizacionExcel' => $this->valorizacionExcel($params),
            'mermasExcel' => $this->mermasExcel($params),
            'rotacionExcel' => $this->rotacionExcel($params),
            'vencidosExcel' => $this->vencidosExcel($params),
            'mermasTotalesExcel' => $this->mermasTotalesExcel($params),
            'stockMinimoExcel' => $this->stockMinimoExcel($params),
            'ajustesExcel' => $this->ajustesExcel($params),
            'costoVentasExcel' => $this->costoVentasExcel($params),
            default => throw new InvalidArgumentException("Reporte Excel '{$reportName}' no soportado."),
        };
    }

    /** @param  array<string, mixed>  $params */
    private function stockPorProductoPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-001');
        $filas = $this->stockPorProducto->ejecutar($this->filtrosStockProducto($params));
        $paginas = $this->reportePaginador->chunkParaPdf($filas);

        return $this->vistaPdf('reports.inventario.stock.stock-por-producto', [
            'paginas' => $paginas,
            'totalRegistros' => $filas->count(),
            'codigoReporte' => 'HTB-INV-001',
            'nombreReporte' => 'Inventario de Productos',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function movimientosPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-003');
        $filtros = $this->filtrosMovimientos($params);
        $movimientos = $this->movimientosInventario->ejecutar($filtros, 500)->items();
        $paginas = $this->reportePaginador->chunkParaPdf(collect($movimientos));

        return $this->vistaPdf('reports.inventario.movimientos.movimientos', [
            'paginas' => $paginas,
            'totalRegistros' => count($movimientos),
            'filtros' => $filtros,
            'codigoReporte' => 'HTB-INV-003',
            'nombreReporte' => 'Movimientos de Inventario',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function cuarentenaPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-004');
        $lotes = $this->lotesCuarentena->ejecutar([
            'producto_id' => $this->enteroFiltro($params, 'producto_id'),
        ]);
        $paginas = $this->reportePaginador->chunkParaPdf($lotes);

        return $this->vistaPdf('reports.inventario.lotes.cuarentena', [
            'paginas' => $paginas,
            'totalRegistros' => $lotes->count(),
            'codigoReporte' => 'HTB-INV-004',
            'nombreReporte' => 'Productos en Cuarentena',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function proximosVencerPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-005');
        $dias = $this->enteroFiltro($params, 'dias') ?? 30;
        $lotes = $this->lotesProximosVencer->ejecutar([
            'dias' => $dias,
            'producto_id' => $this->enteroFiltro($params, 'producto_id'),
        ]);
        $paginas = $this->reportePaginador->chunkParaPdf($lotes);

        return $this->vistaPdf('reports.inventario.lotes.proximos-vencer', [
            'paginas' => $paginas,
            'totalRegistros' => $lotes->count(),
            'dias' => $dias,
            'codigoReporte' => 'HTB-INV-005',
            'nombreReporte' => 'Próximos Vencimientos',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function mermasPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-006');
        $filtros = $this->filtrosMermas($params);
        $lotes = $this->lotesMerma->ejecutar($filtros);
        $paginas = $this->reportePaginador->chunkParaPdf($lotes);

        return $this->vistaPdf('reports.inventario.mermas.mermas', [
            'paginas' => $paginas,
            'totalRegistros' => $lotes->count(),
            'filtros' => $filtros,
            'codigoReporte' => 'HTB-INV-006',
            'nombreReporte' => 'Mermas y Pérdidas',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function valorizacionPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-007');
        $filtros = ['ubicacion_id' => $this->enteroFiltro($params, 'ubicacion_id')];
        $filas = $this->valorizacionInventario->ejecutar($filtros);
        $paginas = $this->reportePaginador->chunkParaPdf($filas);

        return $this->vistaPdf('reports.inventario.stock.valorizacion', [
            'paginas' => $paginas,
            'totalRegistros' => $filas->count(),
            'totalGeneral' => $this->valorizacionInventario->totalGeneral($filtros),
            'simboloMoneda' => $this->simboloMonedaBase(),
            'codigoReporte' => 'HTB-INV-007',
            'nombreReporte' => 'Valorización de Almacén',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function rotacionPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-008');
        $meses = $this->enteroFiltro($params, 'meses') ?? 3;
        $filas = $this->rotacion->ejecutar(['meses' => $meses]);
        $paginas = $this->reportePaginador->chunkParaPdf($filas);

        return $this->vistaPdf('reports.inventario.stock.rotacion', [
            'paginas' => $paginas,
            'totalRegistros' => $filas->count(),
            'meses' => $meses,
            'codigoReporte' => 'HTB-INV-008',
            'nombreReporte' => 'Rotación de Inventario',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function trazabilidadLotePdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-011');
        $loteId = $this->enteroFiltro($params, 'lote_id') ?? 0;
        $data = $this->trazabilidadLote->ejecutar($loteId);
        $paginas = $this->reportePaginador->chunkParaPdf(collect($data->movimientos));

        return $this->vistaPdf('reports.inventario.trazabilidad.trazabilidad-lote', [
            'paginas' => $paginas,
            'totalRegistros' => count($data->movimientos),
            'lote' => $data->lote,
            'codigoReporte' => 'HTB-INV-011',
            'nombreReporte' => 'Trazabilidad de Lote',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function vencidosPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-012');
        $lotes = $this->lotesVencidos->ejecutar([
            'producto_id' => $this->enteroFiltro($params, 'producto_id'),
        ]);
        $paginas = $this->reportePaginador->chunkParaPdf($lotes);

        return $this->vistaPdf('reports.inventario.lotes.vencidos', [
            'paginas' => $paginas,
            'totalRegistros' => $lotes->count(),
            'codigoReporte' => 'HTB-INV-012',
            'nombreReporte' => 'Productos Vencidos',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function stockMinimoPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-009');
        $filtros = ['categoria_id' => $this->enteroFiltro($params, 'categoria_id')];
        $filas = $this->obtenerStockMinimo->ejecutar($filtros);
        $paginas = $this->reportePaginador->chunkParaPdf($filas);

        return $this->vistaPdf('reports.inventario.stock.stock-minimo', [
            'paginas' => $paginas,
            'totalRegistros' => $filas->count(),
            'codigoReporte' => 'HTB-INV-009',
            'nombreReporte' => 'Reporte de Stock Mínimo y Punto de Pedido',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function ajustesPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-010');
        $filtros = $this->filtrosAjustes($params);
        $ajustes = $this->obtenerAjustesInventario->ejecutar($filtros, 500)->items();
        $paginas = $this->reportePaginador->chunkParaPdf(collect($ajustes));

        return $this->vistaPdf('reports.inventario.movimientos.ajustes', [
            'paginas' => $paginas,
            'totalRegistros' => count($ajustes),
            'filtros' => $filtros,
            'codigoReporte' => 'HTB-INV-010',
            'nombreReporte' => 'Auditoría de Ajustes de Inventario',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function costoVentasPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-INV-013');
        $filtros = $this->filtrosCostoVentas($params);
        $filas = $this->obtenerAnalisisCostoVentas->ejecutar($filtros);
        $paginas = $this->reportePaginador->chunkParaPdf($filas);

        return $this->vistaPdf('reports.inventario.stock.costo-ventas', [
            'paginas' => $paginas,
            'totalRegistros' => $filas->count(),
            'filtros' => $filtros,
            'simboloMoneda' => $this->simboloMonedaBase(),
            'codigoReporte' => 'HTB-INV-013',
            'nombreReporte' => 'Análisis de Costo de Ventas',
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function stockPorProductoExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-001');
        $filas = $this->stockPorProducto->ejecutar($this->filtrosStockProducto($params));

        return $this->descargarExcel(
            $filas,
            'HTB-INV-001-Stock-Producto.xlsx',
            'Inventario',
            [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                ColumnaExcel::make('Variante', fn ($r) => $r->variante),
                ColumnaExcel::make('Categoría', fn ($r) => $r->categoria),
                ColumnaExcel::make('Ubicación', fn ($r) => $r->ubicacion),
                ColumnaExcel::make('Stock Disponible', fn ($r) => $r->stockDisponible, numerica: true),
                ColumnaExcel::make('En Cuarentena', fn ($r) => $r->stockCuarentena, numerica: true),
                ColumnaExcel::make('Total Lotes', fn ($r) => $r->totalLotes, numerica: true),
            ],
        );
    }

    /** @param  array<string, mixed>  $params */
    private function movimientosExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-003');
        $movimientos = $this->movimientosInventario->ejecutar($this->filtrosMovimientos($params), 500)->items();

        return $this->descargarExcel(
            collect($movimientos),
            'HTB-INV-003-Movimientos.xlsx',
            'Movimientos',
            [
                ColumnaExcel::make('Fecha', fn ($r) => $r->created_at?->format('d/m/Y H:i')),
                ColumnaExcel::make('Tipo', fn ($r) => $r->tipo),
                ColumnaExcel::make('Producto', fn ($r) => $r->producto->nombre),
                ColumnaExcel::make('Lote', fn ($r) => $r->lote->codigo_lote ?? 'N/A'),
                ColumnaExcel::make('Origen', fn ($r) => $r->ubicacionOrigen->nombre ?? 'N/A'),
                ColumnaExcel::make('Destino', fn ($r) => $r->ubicacionDestino->nombre ?? 'N/A'),
                ColumnaExcel::make('Cantidad', fn ($r) => (float) $r->cantidad, numerica: true),
                ColumnaExcel::make('Referencia', fn ($r) => $r->referencia ?? 'N/A'),
            ],
        );
    }

    /** @param  array<string, mixed>  $params */
    private function valorizacionExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-007');
        $filtros = ['ubicacion_id' => $this->enteroFiltro($params, 'ubicacion_id')];
        $filas = $this->valorizacionInventario->ejecutar($filtros);

        return $this->descargarExcel(
            $filas,
            'HTB-INV-007-Valorizacion.xlsx',
            'Valorización',
            [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                ColumnaExcel::make('Variante', fn ($r) => $r->variante),
                ColumnaExcel::make('Categoría', fn ($r) => $r->categoria),
                ColumnaExcel::make('Ubicación', fn ($r) => $r->ubicacion),
                ColumnaExcel::make('Stock', fn ($r) => $r->stock, numerica: true),
                ColumnaExcel::make('Costo Unitario', fn ($r) => $r->costoUnitario, numerica: true),
                ColumnaExcel::make('Costo Total', fn ($r) => $r->costoTotal, numerica: true),
            ],
        );
    }

    /** @param  array<string, mixed>  $params */
    private function mermasExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-006');
        $lotes = $this->lotesMerma->ejecutar($this->filtrosMermas($params));

        return $this->descargarExcel(
            $lotes,
            'HTB-INV-006-Mermas.xlsx',
            'Mermas',
            [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto?->nombre),
                ColumnaExcel::make('Lote', fn ($r) => $r->codigo_lote),
                ColumnaExcel::make('Cantidad', fn ($r) => $r->cantidad_merma, numerica: true),
                ColumnaExcel::make('Costo', fn ($r) => $r->costo_total, numerica: true),
                ColumnaExcel::make('Motivo', fn ($r) => $r->motivo),
                ColumnaExcel::make('Fecha', fn ($r) => $r->created_at?->format('d/m/Y H:i')),
            ],
        );
    }

    /** @param  array<string, mixed>  $params */
    private function cuarentenaExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-004');
        $lotes = $this->lotesCuarentena->ejecutar([
            'producto_id' => $this->enteroFiltro($params, 'producto_id'),
        ]);

        return $this->descargarExcel(
            $lotes,
            'HTB-INV-004-Cuarentena.xlsx',
            'Cuarentena',
            [
                ColumnaExcel::make('Código de Lote', fn ($r) => $r->codigo_lote),
                ColumnaExcel::make('Producto', fn ($r) => $r->producto?->nombre),
                ColumnaExcel::make('Variante', fn ($r) => $r->variante->nombre_variante ?? $r->variante->codigo ?? 'N/A'),
                ColumnaExcel::make('Ubicación', fn ($r) => $r->ubicacion->nombre ?? 'N/A'),
                ColumnaExcel::make('Cantidad Inicial', fn ($r) => $r->cantidad_inicial, numerica: true),
                ColumnaExcel::make('Disponible', fn ($r) => $r->cantidad_disponible, numerica: true),
                ColumnaExcel::make('Fecha Retención', fn ($r) => $r->updated_at?->format('d/m/Y H:i')),
            ],
        );
    }

    /** @param  array<string, mixed>  $params */
    private function proximosVencerExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-005');
        $dias = $this->enteroFiltro($params, 'dias') ?? 30;
        $lotes = $this->lotesProximosVencer->ejecutar([
            'dias' => $dias,
            'producto_id' => $this->enteroFiltro($params, 'producto_id'),
        ]);

        return $this->descargarExcel(
            $lotes,
            'HTB-INV-005-Proximos-Vencer.xlsx',
            'Próximos a Vencer',
            [
                ColumnaExcel::make('Código de Lote', fn ($r) => $r->codigo_lote),
                ColumnaExcel::make('Producto', fn ($r) => $r->producto?->nombre),
                ColumnaExcel::make('Variante', fn ($r) => $r->variante->nombre_variante ?? $r->variante->codigo ?? 'N/A'),
                ColumnaExcel::make('Ubicación', fn ($r) => $r->ubicacion->nombre ?? 'N/A'),
                ColumnaExcel::make('Cantidad Disponible', fn ($r) => $r->cantidad_disponible, numerica: true),
                ColumnaExcel::make('Fecha Vencimiento', fn ($r) => $r->fecha_vencimiento?->format('d/m/Y')),
                ColumnaExcel::make('Días Restantes', fn ($r) => now()->diffInDays($r->fecha_vencimiento, false), numerica: true),
            ],
        );
    }

    /** @param  array<string, mixed>  $params */
    private function vencidosExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-012');
        $lotes = $this->lotesVencidos->ejecutar([
            'producto_id' => $this->enteroFiltro($params, 'producto_id'),
        ]);

        return $this->descargarExcel(
            $lotes,
            'HTB-INV-012-Lotes-Vencidos.xlsx',
            'Vencidos',
            [
                ColumnaExcel::make('Código de Lote', fn ($r) => $r->codigo_lote),
                ColumnaExcel::make('Producto', fn ($r) => $r->producto?->nombre),
                ColumnaExcel::make('Variante', fn ($r) => $r->variante->nombre_variante ?? $r->variante->codigo ?? 'N/A'),
                ColumnaExcel::make('Ubicación', fn ($r) => $r->ubicacion->nombre ?? 'N/A'),
                ColumnaExcel::make('Cantidad Disponible', fn ($r) => $r->cantidad_disponible, numerica: true),
                ColumnaExcel::make('Fecha Vencimiento', fn ($r) => $r->fecha_vencimiento?->format('d/m/Y')),
                ColumnaExcel::make('Días de Vencido', fn ($r) => abs((int) now()->diffInDays($r->fecha_vencimiento, false)), numerica: true),
            ],
        );
    }

    /** @param  array<string, mixed>  $params */
    private function rotacionExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-008');
        $meses = $this->enteroFiltro($params, 'meses') ?? 3;
        $filas = $this->rotacion->ejecutar(['meses' => $meses]);

        return $this->descargarExcel(
            $filas,
            'HTB-INV-008-Rotacion.xlsx',
            'Rotación',
            [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                ColumnaExcel::make('Total Salidas', fn ($r) => $r->totalSalidas, numerica: true),
                ColumnaExcel::make('Stock Promedio', fn ($r) => $r->stockPromedio, numerica: true),
                ColumnaExcel::make('Índice Rotación', fn ($r) => $r->indiceRotacion, numerica: true),
                ColumnaExcel::make('Clasificación', fn ($r) => $r->clasificacion),
            ],
        );
    }

    /** @param  array<string, mixed>  $params */
    private function mermasTotalesExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-014');
        $filas = $this->mermasTotales->ejecutar([
            'periodo_desde' => $this->fechaFiltro($params, 'periodo_desde', now()->startOfMonth()->toDateString()),
            'periodo_hasta' => $this->fechaFiltro($params, 'periodo_hasta', now()->toDateString()),
        ]);

        return $this->descargarExcel(
            $filas,
            'HTB-INV-014-Mermas-Totales.xlsx',
            'Mermas Totales',
            [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                ColumnaExcel::make('Categoría', fn ($r) => $r->categoria),
                ColumnaExcel::make('Tipo Movimiento', fn ($r) => $r->tipoMovimiento),
                ColumnaExcel::make('Referencia', fn ($r) => $r->referencia ?? 'N/A'),
                ColumnaExcel::make('Cantidad Perdida', fn ($r) => $r->cantidadPerdida, numerica: true),
                ColumnaExcel::make('Costo Unitario', fn ($r) => $r->costoUnitario, numerica: true),
                ColumnaExcel::make('Pérdida Total', fn ($r) => $r->perdidaTotal, numerica: true),
            ],
        );
    }

    /** @param  array<string, mixed>  $params */
    private function stockMinimoExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-009');
        $filtros = ['categoria_id' => $this->enteroFiltro($params, 'categoria_id')];
        $filas = $this->obtenerStockMinimo->ejecutar($filtros);

        return $this->descargarExcel(
            $filas,
            'HTB-INV-009-Stock-Minimo.xlsx',
            'Stock Mínimo',
            [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                ColumnaExcel::make('Variante', fn ($r) => $r->variante),
                ColumnaExcel::make('Categoría', fn ($r) => $r->categoria),
                ColumnaExcel::make('Stock Actual', fn ($r) => $r->stockActual, numerica: true),
                ColumnaExcel::make('Punto de Pedido', fn ($r) => $r->puntoPedido, numerica: true),
                ColumnaExcel::make('Pendiente Reabastecer', fn ($r) => $r->pendienteReplenish, numerica: true),
                ColumnaExcel::make('Estado', fn ($r) => $r->estado),
            ],
        );
    }

    /** @param  array<string, mixed>  $params */
    private function ajustesExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-010');
        $ajustes = $this->obtenerAjustesInventario->ejecutar($this->filtrosAjustes($params), 500)->items();

        return $this->descargarExcel(
            collect($ajustes),
            'HTB-INV-010-Ajustes.xlsx',
            'Ajustes',
            [
                ColumnaExcel::make('Fecha', fn ($r) => $r->created_at?->format('d/m/Y H:i')),
                ColumnaExcel::make('Producto', fn ($r) => $r->producto->nombre),
                ColumnaExcel::make('Lote', fn ($r) => $r->lote->codigo_lote ?? 'N/A'),
                ColumnaExcel::make('Origen', fn ($r) => $r->ubicacionOrigen->nombre ?? 'N/A'),
                ColumnaExcel::make('Destino', fn ($r) => $r->ubicacionDestino->nombre ?? 'N/A'),
                ColumnaExcel::make('Cantidad', fn ($r) => (float) $r->cantidad, numerica: true),
                ColumnaExcel::make('Responsable', fn ($r) => $r->usuario_nombre),
                ColumnaExcel::make('Motivo', fn ($r) => $r->referencia ?? 'Ajuste manual'),
            ],
        );
    }

    /** @param  array<string, mixed>  $params */
    private function costoVentasExcel(array $params): StreamedResponse
    {
        $this->auditoria('HTB-INV-013');
        $filas = $this->obtenerAnalisisCostoVentas->ejecutar($this->filtrosCostoVentas($params));

        return $this->descargarExcel(
            $filas,
            'HTB-INV-013-Costo-Ventas.xlsx',
            'Costo de Ventas',
            [
                ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                ColumnaExcel::make('Variante', fn ($r) => $r->variante),
                ColumnaExcel::make('Categoría', fn ($r) => $r->categoria),
                ColumnaExcel::make('Cant. Comprada', fn ($r) => $r->cantidadComprada, numerica: true),
                ColumnaExcel::make('Costo Compras', fn ($r) => $r->costoCompras, numerica: true),
                ColumnaExcel::make('Cant. Consumida', fn ($r) => $r->cantidadConsumida, numerica: true),
                ColumnaExcel::make('Costo Consumo', fn ($r) => $r->costoConsumo, numerica: true),
                ColumnaExcel::make('Desviación %', fn ($r) => $r->desviacionPorcentaje, numerica: true),
            ],
        );
    }

    private function auditoria(string $codigo): void
    {
        $this->auditoria->ejecutar($codigo, [
            'usuario' => auth()->id(),
            'ip' => request()->ip(),
        ]);
    }

    /** @param  array<string, mixed>  $params
     * @return array{producto_id: int|null, ubicacion_id: int|null}
     */
    private function filtrosStockProducto(array $params): array
    {
        return [
            'producto_id' => $this->enteroFiltro($params, 'producto_id'),
            'ubicacion_id' => $this->enteroFiltro($params, 'ubicacion_id'),
        ];
    }

    /** @param  array<string, mixed>  $params
     * @return array{tipo: string, producto_id: int|null, fecha_desde: string, fecha_hasta: string}
     */
    private function filtrosMovimientos(array $params): array
    {
        return [
            'tipo' => $this->textoFiltro($params, 'tipo'),
            'producto_id' => $this->enteroFiltro($params, 'producto_id'),
            'fecha_desde' => $this->fechaFiltro($params, 'fecha_desde', now()->startOfMonth()->toDateString()),
            'fecha_hasta' => $this->fechaFiltro($params, 'fecha_hasta', now()->toDateString()),
        ];
    }

    /** @param  array<string, mixed>  $params
     * @return array{producto_id: int|null, fecha_desde: string, fecha_hasta: string}
     */
    private function filtrosAjustes(array $params): array
    {
        return [
            'producto_id' => $this->enteroFiltro($params, 'producto_id'),
            'fecha_desde' => $this->fechaFiltro($params, 'fecha_desde', now()->startOfMonth()->toDateString()),
            'fecha_hasta' => $this->fechaFiltro($params, 'fecha_hasta', now()->toDateString()),
        ];
    }

    /** @param  array<string, mixed>  $params
     * @return array{fecha_desde: string, fecha_hasta: string}
     */
    private function filtrosCostoVentas(array $params): array
    {
        return [
            'fecha_desde' => $this->fechaFiltro($params, 'fecha_desde', now()->startOfMonth()->toDateString()),
            'fecha_hasta' => $this->fechaFiltro($params, 'fecha_hasta', now()->toDateString()),
        ];
    }

    /** @param  array<string, mixed>  $params
     * @return array{periodo_desde: string, periodo_hasta: string, motivo: string}
     */
    private function filtrosMermas(array $params): array
    {
        return [
            'periodo_desde' => $this->fechaFiltro($params, 'periodo_desde', now()->startOfMonth()->toDateString()),
            'periodo_hasta' => $this->fechaFiltro($params, 'periodo_hasta', now()->toDateString()),
            'motivo' => $this->textoFiltro($params, 'motivo'),
        ];
    }

    private function simboloMonedaBase(): string
    {
        $monedaBase = $this->obtenerMonedaBase->ejecutar();

        return (string) ($monedaBase->simbolo ?? 'C$');
    }

    /** @param  array<string, mixed>  $params */
    private function enteroFiltro(array $params, string $campo): ?int
    {
        $valor = $params[$campo] ?? null;

        return is_numeric($valor) ? (int) $valor : null;
    }

    /** @param  array<string, mixed>  $params */
    private function textoFiltro(array $params, string $campo): string
    {
        $valor = $params[$campo] ?? null;

        return is_scalar($valor) ? (string) $valor : '';
    }

    /** @param  array<string, mixed>  $params */
    private function fechaFiltro(array $params, string $campo, string $predeterminado): string
    {
        $valor = $params[$campo] ?? null;

        return is_scalar($valor) ? (string) $valor : $predeterminado;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function vistaPdf(string $vista, array $data): DomPdfInstance
    {
        return Pdf::loadView($vista, array_merge(HotelInfo::getBaseData(), $data))->setPaper('letter');
    }

    /**
     * @template TValue
     *
     * @param  Collection<int, TValue>  $coleccion
     * @param  array<int, ColumnaExcel>  $columnas
     */
    private function descargarExcel(Collection $coleccion, string $nombre, string $hoja, array $columnas): StreamedResponse
    {
        return (new GeneradorExcel)->descargar(
            coleccion: $coleccion,
            nombre: $nombre,
            hoja: $hoja,
            columnas: $columnas,
        );
    }
}
