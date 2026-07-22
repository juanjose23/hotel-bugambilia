<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Reportes;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesCuarentena;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesProximosVencer;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesVencidos;
use App\Repository\Queries\Inventario\Gestion\ObtenerRotacionInventario;
use App\Repository\Queries\Inventario\Mermas\ObtenerLotesMerma;
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
        $monedaBase = $this->obtenerMonedaBase->ejecutar();
        $simboloMoneda = (string) ($monedaBase->simbolo ?? 'C$');
        switch ($reportName) {
            case 'stockPorProductoPdf':
                $this->auditoria('HTB-INV-001');
                $productoId = isset($params['producto_id']) && is_numeric($params['producto_id']) ? (int) $params['producto_id'] : null;
                $ubicacionId = isset($params['ubicacion_id']) && is_numeric($params['ubicacion_id']) ? (int) $params['ubicacion_id'] : null;
                $filas = $this->stockPorProducto->ejecutar([
                    'producto_id' => $productoId,
                    'ubicacion_id' => $ubicacionId,
                ]);
                $paginas = $this->reportePaginador->chunkParaPdf($filas);

                return Pdf::loadView('reports.inventario.stock.stock-por-producto', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => $filas->count(),
                    'codigoReporte' => 'HTB-INV-001',
                    'nombreReporte' => 'Inventario de Productos',
                ]))->setPaper('letter');

            case 'movimientosPdf':
                $this->auditoria('HTB-INV-003');
                $reqTipo = $params['tipo'] ?? '';
                $reqProd = $params['producto_id'] ?? null;
                $reqDesde = $params['fecha_desde'] ?? now()->startOfMonth()->toDateString();
                $reqHasta = $params['fecha_hasta'] ?? now()->toDateString();

                $filtros = [
                    'tipo' => is_scalar($reqTipo) ? (string) $reqTipo : '',
                    'producto_id' => is_numeric($reqProd) ? (int) $reqProd : null,
                    'fecha_desde' => is_scalar($reqDesde) ? (string) $reqDesde : now()->startOfMonth()->toDateString(),
                    'fecha_hasta' => is_scalar($reqHasta) ? (string) $reqHasta : now()->toDateString(),
                ];
                $movimientos = $this->movimientosInventario->ejecutar($filtros, 500)->items();
                $paginas = $this->reportePaginador->chunkParaPdf(collect($movimientos));

                return Pdf::loadView('reports.inventario.movimientos.movimientos', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => count($movimientos),
                    'filtros' => $filtros,
                    'codigoReporte' => 'HTB-INV-003',
                    'nombreReporte' => 'Movimientos de Inventario',
                ]))->setPaper('letter');

            case 'cuarentenaPdf':
                $this->auditoria('HTB-INV-004');
                $reqProd = $params['producto_id'] ?? null;
                $productoId = is_numeric($reqProd) ? (int) $reqProd : null;
                $lotes = $this->lotesCuarentena->ejecutar([
                    'producto_id' => $productoId,
                ]);
                $paginas = $this->reportePaginador->chunkParaPdf($lotes);

                return Pdf::loadView('reports.inventario.lotes.cuarentena', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => $lotes->count(),
                    'codigoReporte' => 'HTB-INV-004',
                    'nombreReporte' => 'Productos en Cuarentena',
                ]))->setPaper('letter');

            case 'proximosVencerPdf':
                $this->auditoria('HTB-INV-005');
                $reqDias = $params['dias'] ?? 30;
                $dias = is_numeric($reqDias) ? (int) $reqDias : 30;
                $reqProd = $params['producto_id'] ?? null;
                $productoId = is_numeric($reqProd) ? (int) $reqProd : null;
                $lotes = $this->lotesProximosVencer->ejecutar([
                    'dias' => $dias,
                    'producto_id' => $productoId,
                ]);
                $paginas = $this->reportePaginador->chunkParaPdf($lotes);

                return Pdf::loadView('reports.inventario.lotes.proximos-vencer', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => $lotes->count(),
                    'dias' => $dias,
                    'codigoReporte' => 'HTB-INV-005',
                    'nombreReporte' => 'Próximos Vencimientos',
                ]))->setPaper('letter');

            case 'mermasPdf':
                $this->auditoria('HTB-INV-006');
                $reqDesde = $params['periodo_desde'] ?? now()->startOfMonth()->toDateString();
                $reqHasta = $params['periodo_hasta'] ?? now()->toDateString();
                $reqMotivo = $params['motivo'] ?? '';

                $filtros = [
                    'periodo_desde' => is_scalar($reqDesde) ? (string) $reqDesde : now()->startOfMonth()->toDateString(),
                    'periodo_hasta' => is_scalar($reqHasta) ? (string) $reqHasta : now()->toDateString(),
                    'motivo' => is_scalar($reqMotivo) ? (string) $reqMotivo : '',
                ];
                $lotes = $this->lotesMerma->ejecutar($filtros);
                $paginas = $this->reportePaginador->chunkParaPdf($lotes);

                return Pdf::loadView('reports.inventario.mermas.mermas', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => $lotes->count(),
                    'filtros' => $filtros,
                    'codigoReporte' => 'HTB-INV-006',
                    'nombreReporte' => 'Mermas y Pérdidas',
                ]))->setPaper('letter');

            case 'valorizacionPdf':
                $this->auditoria('HTB-INV-007');
                $reqUbi = $params['ubicacion_id'] ?? null;
                $filtros = ['ubicacion_id' => is_numeric($reqUbi) ? (int) $reqUbi : null];
                $filas = $this->valorizacionInventario->ejecutar($filtros);
                $paginas = $this->reportePaginador->chunkParaPdf($filas);

                return Pdf::loadView('reports.inventario.stock.valorizacion', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => $filas->count(),
                    'totalGeneral' => $this->valorizacionInventario->totalGeneral($filtros),
                    'simboloMoneda' => $simboloMoneda,
                    'codigoReporte' => 'HTB-INV-007',
                    'nombreReporte' => 'Valorización de Almacén',
                ]))->setPaper('letter');

            case 'rotacionPdf':
                $this->auditoria('HTB-INV-008');
                $reqMeses = $params['meses'] ?? 3;
                $meses = is_numeric($reqMeses) ? (int) $reqMeses : 3;
                $filas = $this->rotacion->ejecutar(['meses' => $meses]);
                $paginas = $this->reportePaginador->chunkParaPdf($filas);

                return Pdf::loadView('reports.inventario.stock.rotacion', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => $filas->count(),
                    'meses' => $meses,
                    'codigoReporte' => 'HTB-INV-008',
                    'nombreReporte' => 'Rotación de Inventario',
                ]))->setPaper('letter');

            case 'trazabilidadLotePdf':
                $this->auditoria('HTB-INV-011');
                $loteId = isset($params['lote_id']) && is_numeric($params['lote_id']) ? (int) $params['lote_id'] : 0;
                $data = $this->trazabilidadLote->ejecutar($loteId);
                $paginas = $this->reportePaginador->chunkParaPdf(collect($data->movimientos));

                return Pdf::loadView('reports.inventario.trazabilidad.trazabilidad-lote', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => count($data->movimientos),
                    'lote' => $data->lote,
                    'codigoReporte' => 'HTB-INV-011',
                    'nombreReporte' => 'Trazabilidad de Lote',
                ]))->setPaper('letter');

            case 'vencidosPdf':
                $this->auditoria('HTB-INV-012');
                $reqProd = $params['producto_id'] ?? null;
                $productoId = is_numeric($reqProd) ? (int) $reqProd : null;
                $lotes = $this->lotesVencidos->ejecutar([
                    'producto_id' => $productoId,
                ]);
                $paginas = $this->reportePaginador->chunkParaPdf($lotes);

                return Pdf::loadView('reports.inventario.lotes.vencidos', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => $lotes->count(),
                    'codigoReporte' => 'HTB-INV-012',
                    'nombreReporte' => 'Productos Vencidos',
                ]))->setPaper('letter');

            case 'stockMinimoPdf':
                $this->auditoria('HTB-INV-009');
                $reqCat = $params['categoria_id'] ?? null;
                $filtros = ['categoria_id' => is_numeric($reqCat) ? (int) $reqCat : null];
                $filas = $this->obtenerStockMinimo->ejecutar($filtros);
                $paginas = $this->reportePaginador->chunkParaPdf($filas);

                return Pdf::loadView('reports.inventario.stock.stock-minimo', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => $filas->count(),
                    'codigoReporte' => 'HTB-INV-009',
                    'nombreReporte' => 'Reporte de Stock Mínimo y Punto de Pedido',
                ]))->setPaper('letter');

            case 'ajustesPdf':
                $this->auditoria('HTB-INV-010');
                $reqProd = $params['producto_id'] ?? null;
                $reqDesde = $params['fecha_desde'] ?? now()->startOfMonth()->toDateString();
                $reqHasta = $params['fecha_hasta'] ?? now()->toDateString();

                $filtros = [
                    'producto_id' => is_numeric($reqProd) ? (int) $reqProd : null,
                    'fecha_desde' => is_scalar($reqDesde) ? (string) $reqDesde : now()->startOfMonth()->toDateString(),
                    'fecha_hasta' => is_scalar($reqHasta) ? (string) $reqHasta : now()->toDateString(),
                ];
                $ajustes = $this->obtenerAjustesInventario->ejecutar($filtros, 500)->items();
                $paginas = $this->reportePaginador->chunkParaPdf(collect($ajustes));

                return Pdf::loadView('reports.inventario.movimientos.ajustes', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => count($ajustes),
                    'filtros' => $filtros,
                    'codigoReporte' => 'HTB-INV-010',
                    'nombreReporte' => 'Auditoría de Ajustes de Inventario',
                ]))->setPaper('letter');

            case 'costoVentasPdf':
                $this->auditoria('HTB-INV-013');
                $reqDesde = $params['fecha_desde'] ?? now()->startOfMonth()->toDateString();
                $reqHasta = $params['fecha_hasta'] ?? now()->toDateString();

                $filtros = [
                    'fecha_desde' => is_scalar($reqDesde) ? (string) $reqDesde : now()->startOfMonth()->toDateString(),
                    'fecha_hasta' => is_scalar($reqHasta) ? (string) $reqHasta : now()->toDateString(),
                ];
                $filas = $this->obtenerAnalisisCostoVentas->ejecutar($filtros);
                $paginas = $this->reportePaginador->chunkParaPdf($filas);

                return Pdf::loadView('reports.inventario.stock.costo-ventas', array_merge(HotelInfo::getBaseData(), [
                    'paginas' => $paginas,
                    'totalRegistros' => $filas->count(),
                    'filtros' => $filtros,
                    'simboloMoneda' => $simboloMoneda,
                    'codigoReporte' => 'HTB-INV-013',
                    'nombreReporte' => 'Análisis de Costo de Ventas',
                ]))->setPaper('letter');

            default:
                throw new \InvalidArgumentException("Reporte '{$reportName}' no soportado.");
        }
    }

    private function auditoria(string $codigo): void
    {
        $this->auditoria->ejecutar($codigo, [
            'usuario' => auth()->id(),
            'ip' => request()->ip(),
        ]);
    }

    /** @param  array<string, mixed>  $params */
    public function executeExcel(string $reportName, array $params = []): StreamedResponse
    {
        $generador = new GeneradorExcel;

        switch ($reportName) {
            case 'stockPorProductoExcel':
                $this->auditoria('HTB-INV-001');
                $productoId = isset($params['producto_id']) && is_numeric($params['producto_id']) ? (int) $params['producto_id'] : null;
                $ubicacionId = isset($params['ubicacion_id']) && is_numeric($params['ubicacion_id']) ? (int) $params['ubicacion_id'] : null;
                $filas = $this->stockPorProducto->ejecutar([
                    'producto_id' => $productoId,
                    'ubicacion_id' => $ubicacionId,
                ]);

                return $generador->descargar(
                    coleccion: $filas,
                    nombre: 'HTB-INV-001-Stock-Producto.xlsx',
                    hoja: 'Inventario',
                    columnas: [
                        ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                        ColumnaExcel::make('Variante', fn ($r) => $r->variante),
                        ColumnaExcel::make('Categoría', fn ($r) => $r->categoria),
                        ColumnaExcel::make('Ubicación', fn ($r) => $r->ubicacion),
                        ColumnaExcel::make('Stock Disponible', fn ($r) => $r->stockDisponible, numerica: true),
                        ColumnaExcel::make('En Cuarentena', fn ($r) => $r->stockCuarentena, numerica: true),
                        ColumnaExcel::make('Total Lotes', fn ($r) => $r->totalLotes, numerica: true),
                    ],
                );

            case 'movimientosExcel':
                $this->auditoria('HTB-INV-003');
                $reqTipo = $params['tipo'] ?? '';
                $reqProd = $params['producto_id'] ?? null;
                $reqDesde = $params['fecha_desde'] ?? now()->startOfMonth()->toDateString();
                $reqHasta = $params['fecha_hasta'] ?? now()->toDateString();
                $filtros = [
                    'tipo' => is_scalar($reqTipo) ? (string) $reqTipo : '',
                    'producto_id' => is_numeric($reqProd) ? (int) $reqProd : null,
                    'fecha_desde' => is_scalar($reqDesde) ? (string) $reqDesde : now()->startOfMonth()->toDateString(),
                    'fecha_hasta' => is_scalar($reqHasta) ? (string) $reqHasta : now()->toDateString(),
                ];
                $movimientos = $this->movimientosInventario->ejecutar($filtros, 500)->items();

                return $generador->descargar(
                    coleccion: collect($movimientos),
                    nombre: 'HTB-INV-003-Movimientos.xlsx',
                    hoja: 'Movimientos',
                    columnas: [
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

            case 'valorizacionExcel':
                $this->auditoria('HTB-INV-007');
                $reqUbi = $params['ubicacion_id'] ?? null;
                $filtros = ['ubicacion_id' => is_numeric($reqUbi) ? (int) $reqUbi : null];
                $filas = $this->valorizacionInventario->ejecutar($filtros);

                return $generador->descargar(
                    coleccion: $filas,
                    nombre: 'HTB-INV-007-Valorizacion.xlsx',
                    hoja: 'Valorización',
                    columnas: [
                        ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                        ColumnaExcel::make('Variante', fn ($r) => $r->variante),
                        ColumnaExcel::make('Categoría', fn ($r) => $r->categoria),
                        ColumnaExcel::make('Ubicación', fn ($r) => $r->ubicacion),
                        ColumnaExcel::make('Stock', fn ($r) => $r->stock, numerica: true),
                        ColumnaExcel::make('Costo Unitario', fn ($r) => $r->costoUnitario, numerica: true),
                        ColumnaExcel::make('Costo Total', fn ($r) => $r->costoTotal, numerica: true),
                    ],
                );

            case 'mermasExcel':
                $this->auditoria('HTB-INV-006');
                $reqDesde = $params['periodo_desde'] ?? now()->startOfMonth()->toDateString();
                $reqHasta = $params['periodo_hasta'] ?? now()->toDateString();
                $reqMotivo = $params['motivo'] ?? '';
                $filtros = [
                    'periodo_desde' => is_scalar($reqDesde) ? (string) $reqDesde : now()->startOfMonth()->toDateString(),
                    'periodo_hasta' => is_scalar($reqHasta) ? (string) $reqHasta : now()->toDateString(),
                    'motivo' => is_scalar($reqMotivo) ? (string) $reqMotivo : '',
                ];
                $lotes = $this->lotesMerma->ejecutar($filtros);

                return $generador->descargar(
                    coleccion: $lotes,
                    nombre: 'HTB-INV-006-Mermas.xlsx',
                    hoja: 'Mermas',
                    columnas: [
                        ColumnaExcel::make('Producto', fn ($r) => $r->producto?->nombre),
                        ColumnaExcel::make('Lote', fn ($r) => $r->codigo_lote),
                        ColumnaExcel::make('Cantidad', fn ($r) => $r->cantidad_merma, numerica: true),
                        ColumnaExcel::make('Costo', fn ($r) => $r->costo_total, numerica: true),
                        ColumnaExcel::make('Motivo', fn ($r) => $r->motivo),
                        ColumnaExcel::make('Fecha', fn ($r) => $r->created_at?->format('d/m/Y H:i')),
                    ],
                );

            case 'stockMinimoExcel':
                $this->auditoria('HTB-INV-009');
                $reqCat = $params['categoria_id'] ?? null;
                $filtros = ['categoria_id' => is_numeric($reqCat) ? (int) $reqCat : null];
                $filas = $this->obtenerStockMinimo->ejecutar($filtros);

                return $generador->descargar(
                    coleccion: $filas,
                    nombre: 'HTB-INV-009-Stock-Minimo.xlsx',
                    hoja: 'Stock Mínimo',
                    columnas: [
                        ColumnaExcel::make('Producto', fn ($r) => $r->producto),
                        ColumnaExcel::make('Variante', fn ($r) => $r->variante),
                        ColumnaExcel::make('Categoría', fn ($r) => $r->categoria),
                        ColumnaExcel::make('Stock Actual', fn ($r) => $r->stockActual, numerica: true),
                        ColumnaExcel::make('Punto de Pedido', fn ($r) => $r->puntoPedido, numerica: true),
                        ColumnaExcel::make('Pendiente Reabastecer', fn ($r) => $r->pendienteReplenish, numerica: true),
                        ColumnaExcel::make('Estado', fn ($r) => $r->estado),
                    ],
                );

            case 'ajustesExcel':
                $this->auditoria('HTB-INV-010');
                $reqProd = $params['producto_id'] ?? null;
                $reqDesde = $params['fecha_desde'] ?? now()->startOfMonth()->toDateString();
                $reqHasta = $params['fecha_hasta'] ?? now()->toDateString();
                $filtros = [
                    'producto_id' => is_numeric($reqProd) ? (int) $reqProd : null,
                    'fecha_desde' => is_scalar($reqDesde) ? (string) $reqDesde : now()->startOfMonth()->toDateString(),
                    'fecha_hasta' => is_scalar($reqHasta) ? (string) $reqHasta : now()->toDateString(),
                ];
                $ajustes = $this->obtenerAjustesInventario->ejecutar($filtros, 500)->items();

                return $generador->descargar(
                    coleccion: collect($ajustes),
                    nombre: 'HTB-INV-010-Ajustes.xlsx',
                    hoja: 'Ajustes',
                    columnas: [
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

            case 'costoVentasExcel':
                $this->auditoria('HTB-INV-013');
                $reqDesde = $params['fecha_desde'] ?? now()->startOfMonth()->toDateString();
                $reqHasta = $params['fecha_hasta'] ?? now()->toDateString();
                $filtros = [
                    'fecha_desde' => is_scalar($reqDesde) ? (string) $reqDesde : now()->startOfMonth()->toDateString(),
                    'fecha_hasta' => is_scalar($reqHasta) ? (string) $reqHasta : now()->toDateString(),
                ];
                $filas = $this->obtenerAnalisisCostoVentas->ejecutar($filtros);

                return $generador->descargar(
                    coleccion: $filas,
                    nombre: 'HTB-INV-013-Costo-Ventas.xlsx',
                    hoja: 'Costo de Ventas',
                    columnas: [
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

            default:
                throw new \InvalidArgumentException("Reporte Excel '{$reportName}' no soportado.");
        }
    }
}
