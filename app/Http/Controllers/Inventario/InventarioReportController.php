<?php

namespace App\Http\Controllers\Inventario;

use App\Exports\Inventario\LotesCuarentenaExport;
use App\Exports\Inventario\LotesMermaExport;
use App\Exports\Inventario\LotesProximosVencerExport;
use App\Exports\Inventario\LotesVencidosExport;
use App\Exports\Inventario\MermasTotalesExport;
use App\Exports\Inventario\MovimientosInventarioExport;
use App\Exports\Inventario\RotacionInventarioExport;
use App\Exports\Inventario\StockPorProductoExport;
use App\Exports\Inventario\ValorizacionInventarioExport;
use App\Http\Controllers\Controller;
use App\Support\HotelInfo;
use App\Support\ReportePaginador;
use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesCuarentena;
use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesProximosVencer;
use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesVencidos;
use App\UseCases\Inventario\Queries\Mermas\ObtenerLotesMerma;
use App\UseCases\Inventario\Queries\Stock\ObtenerMovimientosInventario;
use App\UseCases\Inventario\Queries\Stock\ObtenerStockPorProducto;
use App\UseCases\Inventario\Queries\Stock\ObtenerValorizacionInventario;
use App\UseCases\Inventario\Queries\Trazabilidad\TrazabilidadLoteHaciaAdelante;
use App\UseCases\Reportes\Mutations\RegistrarAuditoriaReporteUseCase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InventarioReportController extends Controller
{
    private function auditoria(string $codigo): void
    {
        app(RegistrarAuditoriaReporteUseCase::class)->ejecutar($codigo, [
            'usuario' => auth()->id(),
            'ip' => request()->ip(),
        ]);
    }

    // ─── HTB-INV-001: Stock por Producto ─────────────────────────────────

    public function stockPorProductoPdf(): PdfBuilder
    {
        $this->authorize('Inventario:ReporteStock');
        $this->auditoria('HTB-INV-001');

        $reqProd = request('producto_id');
        $productoId = is_numeric($reqProd) ? (int) $reqProd : null;
        $reqUbi = request('ubicacion_id');
        $ubicacionId = is_numeric($reqUbi) ? (int) $reqUbi : null;

        $filas = app(ObtenerStockPorProducto::class)->ejecutar([
            'producto_id' => $productoId,
            'ubicacion_id' => $ubicacionId,
        ]);

        return Pdf::view('reports.inventario.stock-por-producto', array_merge(HotelInfo::getBaseData(), [
            'filas' => $filas,
        ]))->name('HTB-INV-001-Stock-Producto.pdf')->download();
    }

    public function stockPorProductoExcel(): BinaryFileResponse
    {
        $this->authorize('Inventario:ReporteStock');
        $this->auditoria('HTB-INV-001');

        return Excel::download(new StockPorProductoExport([
            'producto_id' => request('producto_id'),
            'ubicacion_id' => request('ubicacion_id'),
        ]), 'HTB-INV-001-Stock-Producto.xlsx');
    }

    // ─── HTB-INV-003: Movimientos ─────────────────────────────────────────

    public function movimientosPdf(): PdfBuilder
    {
        $this->authorize('Inventario:ReporteMovimientos');
        $this->auditoria('HTB-INV-003');

        $reqTipo = request('tipo');
        $reqProd = request('producto_id');
        $reqDesde = request('fecha_desde', now()->startOfMonth()->toDateString());
        $reqHasta = request('fecha_hasta', now()->toDateString());

        $filtros = [
            'tipo' => is_scalar($reqTipo) ? (string) $reqTipo : '',
            'producto_id' => is_numeric($reqProd) ? (int) $reqProd : null,
            'fecha_desde' => is_scalar($reqDesde) ? (string) $reqDesde : now()->startOfMonth()->toDateString(),
            'fecha_hasta' => is_scalar($reqHasta) ? (string) $reqHasta : now()->toDateString(),
        ];

        $movimientos = app(ObtenerMovimientosInventario::class)->ejecutar($filtros, 500)->items();

        return Pdf::view('reports.inventario.movimientos', array_merge(HotelInfo::getBaseData(), [
            'movimientos' => $movimientos,
            'filtros' => $filtros,
        ]))->name('HTB-INV-003-Movimientos.pdf')->download();
    }

    public function movimientosExcel(): BinaryFileResponse
    {
        $this->authorize('Inventario:ReporteMovimientos');
        $this->auditoria('HTB-INV-003');

        return Excel::download(new MovimientosInventarioExport([
            'tipo' => request('tipo'),
            'producto_id' => request('producto_id'),
            'fecha_desde' => request('fecha_desde', now()->startOfMonth()->toDateString()),
            'fecha_hasta' => request('fecha_hasta', now()->toDateString()),
        ]), 'HTB-INV-003-Movimientos.xlsx');
    }

    // ─── HTB-INV-004: Cuarentena ──────────────────────────────────────────

    public function cuarentenaPdf(): PdfBuilder
    {
        $this->authorize('Inventario:ReporteCuarentena');
        $this->auditoria('HTB-INV-004');

        $reqProd = request('producto_id');
        $productoId = is_numeric($reqProd) ? (int) $reqProd : null;
        $lotes = app(ObtenerLotesCuarentena::class)->ejecutar([
            'producto_id' => $productoId,
        ]);
        $paginas = ReportePaginador::chunkParaPdf($lotes);

        return Pdf::view('reports.inventario.cuarentena', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
            'totalRegistros' => $lotes->count(),
        ]))->name('HTB-INV-004-Cuarentena.pdf')->download();
    }

    public function cuarentenaExcel(): BinaryFileResponse
    {
        $this->authorize('Inventario:ReporteCuarentena');
        $this->auditoria('HTB-INV-004');

        return Excel::download(new LotesCuarentenaExport([
            'producto_id' => request('producto_id'),
        ]), 'HTB-INV-004-Cuarentena.xlsx');
    }

    // ─── HTB-INV-005: Próximos a Vencer ──────────────────────────────────

    public function proximosVencerPdf(): PdfBuilder
    {
        $this->authorize('Inventario:ReporteProximosVencer');
        $this->auditoria('HTB-INV-005');

        $reqDias = request('dias', 30);
        $dias = is_numeric($reqDias) ? (int) $reqDias : 30;
        $reqProd = request('producto_id');
        $productoId = is_numeric($reqProd) ? (int) $reqProd : null;
        $lotes = app(ObtenerLotesProximosVencer::class)->ejecutar([
            'dias' => $dias,
            'producto_id' => $productoId,
        ]);
        $paginas = ReportePaginador::chunkParaPdf($lotes);

        return Pdf::view('reports.inventario.proximos-vencer', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
            'totalRegistros' => $lotes->count(),
            'dias' => $dias,
        ]))->name("HTB-INV-005-Proximos-Vencer-{$dias}d.pdf")->download();
    }

    public function proximosVencerExcel(): BinaryFileResponse
    {
        $this->authorize('Inventario:ReporteProximosVencer');
        $this->auditoria('HTB-INV-005');

        $reqDias = request('dias', 30);
        $dias = is_numeric($reqDias) ? (int) $reqDias : 30;

        return Excel::download(new LotesProximosVencerExport([
            'dias' => $dias,
            'producto_id' => request('producto_id'),
        ]), "HTB-INV-005-Proximos-Vencer-{$dias}d.xlsx");
    }

    // ─── HTB-INV-006: Mermas ──────────────────────────────────────────────

    public function mermasPdf(): PdfBuilder
    {
        $this->authorize('Inventario:ReporteMermas');
        $this->auditoria('HTB-INV-006');

        $reqDesde = request('periodo_desde', now()->startOfMonth()->toDateString());
        $reqHasta = request('periodo_hasta', now()->toDateString());
        $reqMotivo = request('motivo');

        $filtros = [
            'periodo_desde' => is_scalar($reqDesde) ? (string) $reqDesde : now()->startOfMonth()->toDateString(),
            'periodo_hasta' => is_scalar($reqHasta) ? (string) $reqHasta : now()->toDateString(),
            'motivo' => is_scalar($reqMotivo) ? (string) $reqMotivo : '',
        ];

        $lotes = app(ObtenerLotesMerma::class)->ejecutar($filtros);
        $paginas = ReportePaginador::chunkParaPdf($lotes);

        return Pdf::view('reports.inventario.mermas', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
            'totalRegistros' => $lotes->count(),
            'filtros' => $filtros,
        ]))->name('HTB-INV-006-Mermas.pdf')->download();
    }

    public function mermasExcel(): BinaryFileResponse
    {
        $this->authorize('Inventario:ReporteMermas');
        $this->auditoria('HTB-INV-006');

        return Excel::download(new LotesMermaExport([
            'periodo_desde' => request('periodo_desde', now()->startOfMonth()->toDateString()),
            'periodo_hasta' => request('periodo_hasta', now()->toDateString()),
            'motivo' => request('motivo'),
        ]), 'HTB-INV-006-Mermas.xlsx');
    }

    // ─── HTB-INV-007: Valorización ────────────────────────────────────────

    public function valorizacionPdf(): PdfBuilder
    {
        $this->authorize('Inventario:ReporteValorizacion');
        $this->auditoria('HTB-INV-007');

        $uc = app(ObtenerValorizacionInventario::class);
        $reqUbi = request('ubicacion_id');
        $filtros = ['ubicacion_id' => is_numeric($reqUbi) ? (int) $reqUbi : null];
        $filas = $uc->ejecutar($filtros);
        $paginas = ReportePaginador::chunkParaPdf($filas, rowPx: 26);

        return Pdf::view('reports.inventario.valorizacion', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
            'totalRegistros' => $filas->count(),
            'totalGeneral' => $uc->totalGeneral($filtros),
        ]))->name('HTB-INV-007-Valorizacion.pdf')->download();
    }

    public function valorizacionExcel(): BinaryFileResponse
    {
        $this->authorize('Inventario:ReporteValorizacion');
        $this->auditoria('HTB-INV-007');

        return Excel::download(new ValorizacionInventarioExport([
            'ubicacion_id' => request('ubicacion_id'),
        ]), 'HTB-INV-007-Valorizacion.xlsx');
    }

    // ─── HTB-INV-008: Rotación ────────────────────────────────────────────

    public function rotacionExcel(): BinaryFileResponse
    {
        $this->authorize('Inventario:ReporteRotacion');
        $this->auditoria('HTB-INV-008');

        $reqMeses = request('meses', 3);
        $meses = is_numeric($reqMeses) ? (int) $reqMeses : 3;

        return Excel::download(new RotacionInventarioExport(['meses' => $meses]), "HTB-INV-008-Rotacion-{$meses}m.xlsx");
    }

    // ─── HTB-INV-009: Mermas Totales (Pérdidas) ───────────────────────────

    public function mermasTotalesExcel(): BinaryFileResponse
    {
        $this->authorize('Inventario:ReporteMermasTotales');
        $this->auditoria('HTB-INV-009');

        return Excel::download(new MermasTotalesExport([
            'periodo_desde' => request('periodo_desde', now()->startOfMonth()->toDateString()),
            'periodo_hasta' => request('periodo_hasta', now()->toDateString()),
        ]), 'HTB-INV-009-Mermas-Totales.xlsx');
    }

    // ─── HTB-INV-011: Trazabilidad hacia adelante ─────────────────────────

    public function trazabilidadLotePdf(int $loteId): PdfBuilder
    {
        $this->authorize('Inventario:ReporteTrazabilidad');
        $this->auditoria('HTB-INV-011');

        $data = app(TrazabilidadLoteHaciaAdelante::class)->ejecutar($loteId);

        return Pdf::view('reports.inventario.trazabilidad-lote', array_merge(HotelInfo::getBaseData(), [
            'lote' => $data['lote'],
            'movimientos' => $data['movimientos'],
        ]))->name("HTB-INV-011-Trazabilidad-Lote-{$loteId}.pdf")->download();
    }

    // ─── HTB-INV-012: Lotes Vencidos ──────────────────────────────────────

    public function vencidosPdf(): PdfBuilder
    {
        $this->authorize('Inventario:ReporteVencidos');
        $this->auditoria('HTB-INV-012');

        $reqProd = request('producto_id');
        $productoId = is_numeric($reqProd) ? (int) $reqProd : null;
        $lotes = app(ObtenerLotesVencidos::class)->ejecutar([
            'producto_id' => $productoId,
        ]);
        $paginas = ReportePaginador::chunkParaPdf($lotes);

        return Pdf::view('reports.inventario.vencidos', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
            'totalRegistros' => $lotes->count(),
        ]))->name('HTB-INV-012-Lotes-Vencidos.pdf')->download();
    }

    public function vencidosExcel(): BinaryFileResponse
    {
        $this->authorize('Inventario:ReporteVencidos');
        $this->auditoria('HTB-INV-012');

        return Excel::download(new LotesVencidosExport([
            'producto_id' => request('producto_id'),
        ]), 'HTB-INV-012-Lotes-Vencidos.xlsx');
    }
}
