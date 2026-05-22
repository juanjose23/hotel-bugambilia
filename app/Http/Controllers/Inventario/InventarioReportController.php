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
    /** @return array<string, mixed> */
    private function baseData(): array
    {
        $logoPath = public_path('img/logo-horizontal.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode((string) file_get_contents($logoPath));
        }

        return [
            'logo_base64' => $logoBase64,
            'hotelInfo' => [
                'telefono' => '+505 8713 6805',
                'email' => 'recepcion@bugambiliashotel.com',
                'direccion' => 'Salida Sur Estelí, Restaurante Absoluto 1c. Oeste, 2c. Sur, 1c. Oeste',
            ],
            'generadoEn' => now()->format('d/m/Y H:i'),
            'usuario' => auth()->user()->name ?? 'Sistema',
        ];
    }

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

        $filas = app(ObtenerStockPorProducto::class)->ejecutar([
            'producto_id' => request('producto_id'),
            'ubicacion_id' => request('ubicacion_id'),
        ]);

        return Pdf::view('reports.inventario.stock-por-producto', array_merge($this->baseData(), [
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

        $filtros = [
            'tipo' => request('tipo'),
            'producto_id' => request('producto_id'),
            'fecha_desde' => request('fecha_desde', now()->startOfMonth()->toDateString()),
            'fecha_hasta' => request('fecha_hasta', now()->toDateString()),
        ];

        $movimientos = app(ObtenerMovimientosInventario::class)->ejecutar($filtros, 500)->items();

        return Pdf::view('reports.inventario.movimientos', array_merge($this->baseData(), [
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

        $lotes = app(ObtenerLotesCuarentena::class)->ejecutar([
            'producto_id' => request('producto_id'),
        ]);

        return Pdf::view('reports.inventario.cuarentena', array_merge($this->baseData(), [
            'lotes' => $lotes,
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

        $dias = (int) request('dias', 30);
        $lotes = app(ObtenerLotesProximosVencer::class)->ejecutar([
            'dias' => $dias,
            'producto_id' => request('producto_id'),
        ]);

        return Pdf::view('reports.inventario.proximos-vencer', array_merge($this->baseData(), [
            'lotes' => $lotes,
            'dias' => $dias,
        ]))->name("HTB-INV-005-Proximos-Vencer-{$dias}d.pdf")->download();
    }

    public function proximosVencerExcel(): BinaryFileResponse
    {
        $this->authorize('Inventario:ReporteProximosVencer');
        $this->auditoria('HTB-INV-005');

        $dias = (int) request('dias', 30);

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

        $filtros = [
            'periodo_desde' => request('periodo_desde', now()->startOfMonth()->toDateString()),
            'periodo_hasta' => request('periodo_hasta', now()->toDateString()),
            'motivo' => request('motivo'),
        ];

        $lotes = app(ObtenerLotesMerma::class)->ejecutar($filtros);

        return Pdf::view('reports.inventario.mermas', array_merge($this->baseData(), [
            'lotes' => $lotes,
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
        $filtros = ['ubicacion_id' => request('ubicacion_id')];

        return Pdf::view('reports.inventario.valorizacion', array_merge($this->baseData(), [
            'filas' => $uc->ejecutar($filtros),
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

        $meses = (int) request('meses', 3);

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

        return Pdf::view('reports.inventario.trazabilidad-lote', array_merge($this->baseData(), [
            'lote' => $data['lote'],
            'movimientos' => $data['movimientos'],
        ]))->name("HTB-INV-011-Trazabilidad-Lote-{$loteId}.pdf")->download();
    }

    // ─── HTB-INV-012: Lotes Vencidos ──────────────────────────────────────

    public function vencidosPdf(): PdfBuilder
    {
        $this->authorize('Inventario:ReporteVencidos');
        $this->auditoria('HTB-INV-012');

        $lotes = app(ObtenerLotesVencidos::class)->ejecutar([
            'producto_id' => request('producto_id'),
        ]);

        return Pdf::view('reports.inventario.vencidos', array_merge($this->baseData(), [
            'lotes' => $lotes,
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
