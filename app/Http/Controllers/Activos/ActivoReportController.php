<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activos;

use App\Exports\Activos\ActivosExport;
use App\Http\Controllers\Controller;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoMantenimiento;
use App\Support\HotelInfo;
use App\Support\ReportePaginador;
use App\UseCases\Activos\Queries\GenerarEtiquetasActivosUseCase;
use App\UseCases\Activos\Queries\ObtenerActivosPorUbicacionUseCase;
use App\UseCases\Activos\Queries\ObtenerFichasReportesUseCase;
use App\UseCases\Activos\Queries\ObtenerHistorialMovimientosUseCase;
use App\UseCases\Activos\Queries\ObtenerHojaHabitacionEspacioUseCase;
use App\UseCases\Activos\Queries\ObtenerMantenimientosReportesUseCase;
use App\UseCases\Activos\Queries\ObtenerReportesActivosVariosUseCase;
use App\UseCases\Reportes\Mutations\RegistrarAuditoriaReporteUseCase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActivoReportController extends Controller
{
    private function auditoria(string $codigo, mixed $record = null): void
    {
        app(RegistrarAuditoriaReporteUseCase::class)->ejecutar($codigo, [
            'usuario' => auth()->id(),
            'ip' => request()->ip(),
            'referencia_id' => (is_object($record) && method_exists($record, 'getKey')) ? $record->getKey() : null,
        ]);
    }

    // ─── HTB-ACT-001: Inventario General ──────────────────────────────────

    public function inventarioGeneralPdf(ObtenerReportesActivosVariosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-001');

        $filtros = [
            'estado' => request('estado'),
            'producto_id' => request('producto_id'),
            'ubicacion_tipo' => request('ubicacion_tipo'),
        ];

        $activos = $useCase->inventarioGeneral($filtros);
        $paginas = ReportePaginador::chunkParaPdf($activos);

        return Pdf::view('reports.activos.inventario-general', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
        ]))->name('HTB-ACT-001-Inventario-General.pdf')->download();
    }

    public function inventarioGeneralExcel(): BinaryFileResponse
    {
        $this->auditoria('HTB-ACT-001');

        $filtros = [
            'estado' => request('estado'),
            'producto_id' => request('producto_id'),
            'ubicacion_tipo' => request('ubicacion_tipo'),
        ];

        return Excel::download(new ActivosExport($filtros), 'HTB-ACT-001-Inventario-General.xlsx');
    }

    // ─── HTB-ACT-002: Ficha del Activo ────────────────────────────────────

    public function fichaActivoPdf(Activo $activo, ObtenerFichasReportesUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-002', $activo);

        return Pdf::view('reports.activos.activo', array_merge(HotelInfo::getBaseData(), [
            'record' => $useCase->fichaActivo($activo),
        ]))->name("HTB-ACT-002-Ficha-Activo-{$activo->codigo_inventario}.pdf")->download();
    }

    // ─── HTB-ACT-003: Ficha de Mantenimiento ──────────────────────────────

    public function fichaMantenimientoPdf(ActivoMantenimiento $mantenimiento, ObtenerFichasReportesUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-003', $mantenimiento);

        return Pdf::view('reports.activos.mantenimiento', array_merge(HotelInfo::getBaseData(), [
            'record' => $useCase->fichaMantenimiento($mantenimiento),
        ]))->name("HTB-ACT-003-Ficha-Mantenimiento-{$mantenimiento->id}.pdf")->download();
    }

    // ─── HTB-ACT-004: Etiquetas de Activos ────────────────────────────────

    public function etiquetasPdf(GenerarEtiquetasActivosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-004');

        $filtros = [
            'estado' => request('estado'),
            'producto_id' => request('producto_id'),
            'ubicacion_tipo' => request('ubicacion_tipo'),
        ];

        return Pdf::view('reports.catalogos.etiquetas-codigos-barras', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $useCase->ejecutar($filtros),
            'nombreReporte' => 'Etiquetas de Códigos de Barras de Activos',
            'codigoReporte' => 'HTB-ACT-004',
            'fecha' => now()->format('d/m/Y H:i'),
        ]))->name('HTB-ACT-004-Etiquetas-Activos.pdf')->download();
    }

    // ─── HTB-ACT-005: Activos por Ubicación ────────────────────────────────

    public function porUbicacionPdf(ObtenerActivosPorUbicacionUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-005');

        $ubicacionTipo = request('ubicacion_tipo');
        $tipoFiltro = is_scalar($ubicacionTipo) ? (string) $ubicacionTipo : '';

        return Pdf::view('reports.activos.por-ubicacion', array_merge(HotelInfo::getBaseData(), [
            'ubicaciones' => $useCase->ejecutar($tipoFiltro),
        ]))->name('HTB-ACT-005-Activos-por-Ubicacion.pdf')->download();
    }

    // ─── HTB-ACT-006: Historial de Movimientos de Activo ────────────────────

    public function historialMovimientosPdf(ObtenerHistorialMovimientosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-006');

        $reqActivoId = request('activo_id');
        $activoId = is_numeric($reqActivoId) ? (int) $reqActivoId : 0;
        $data = $useCase->ejecutar($activoId);

        $codigoInventario = $data['activo'] ? $data['activo']->codigo_inventario : 'Todos';

        return Pdf::view('reports.activos.historial-movimientos', array_merge($this->baseData(), [
            'activo' => $data['activo'],
            'lineaTiempo' => $data['lineaTiempo'],
            'filtroActivo' => $activoId > 0 ? $data['activo'] : null,
        ]))->name("HTB-ACT-006-Historial-{$codigoInventario}.pdf")->download();
    }

    // ─── HTB-ACT-007: Activos en Mantenimiento ─────────────────────────────

    public function enMantenimientoPdf(ObtenerMantenimientosReportesUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-007');

        $activos = $useCase->enMantenimiento();
        $paginas = ReportePaginador::chunkParaPdf($activos);

        return Pdf::view('reports.activos.en-mantenimiento', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
            'totalRegistros' => $activos->count(),
        ]))->name('HTB-ACT-007-Activos-en-Mantenimiento.pdf')->download();
    }

    // ─── HTB-ACT-008: Garantías Próximas a Vencer ──────────────────────────

    public function garantiasProximasPdf(ObtenerReportesActivosVariosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-008');

        $reqDias = request('dias', 90);
        $dias = is_numeric($reqDias) ? (int) $reqDias : 90;
        $activos = $useCase->garantiasProximas($dias);
        $paginas = ReportePaginador::chunkParaPdf($activos);

        return Pdf::view('reports.activos.garantias-proximas', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
            'totalRegistros' => $activos->count(),
            'dias' => $dias,
        ]))->name('HTB-ACT-008-Garantias-Proximas.pdf')->download();
    }

    // ─── HTB-ACT-009: Activos Dados de Baja ────────────────────────────────

    public function dadosDeBajaPdf(ObtenerReportesActivosVariosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-009');

        $bajas = $useCase->dadosDeBaja();
        $paginas = ReportePaginador::chunkParaPdf($bajas);

        return Pdf::view('reports.activos.dados-de-baja', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
            'totalRegistros' => $bajas->count(),
            'totalValorResidual' => $bajas->sum('valor_residual'),
        ]))->name('HTB-ACT-009-Activos-Dados-de-Baja.pdf')->download();
    }

    // ─── HTB-ACT-010: Activos Extraviados ──────────────────────────────────

    public function extraviadosPdf(ObtenerReportesActivosVariosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-010');

        $activos = $useCase->extraviados();
        $paginas = ReportePaginador::chunkParaPdf($activos, rowPx: 26);

        return Pdf::view('reports.activos.extraviados', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
            'totalRegistros' => $activos->count(),
            'totalCosto' => $activos->sum('costo_adquisicion'),
        ]))->name('HTB-ACT-010-Activos-Extraviados.pdf')->download();
    }

    // ─── HTB-ACT-011: Activos Sin Asignación ───────────────────────────────

    public function sinAsignacionPdf(ObtenerReportesActivosVariosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-011');

        $activos = $useCase->sinAsignacion();
        $paginas = ReportePaginador::chunkParaPdf($activos);

        return Pdf::view('reports.activos.sin-asignacion', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
            'totalRegistros' => $activos->count(),
        ]))->name('HTB-ACT-011-Activos-Sin-Asignacion.pdf')->download();
    }

    // ─── HTB-ACT-012: Mantenimientos Vencidos ──────────────────────────────

    public function mantenimientosVencidosPdf(ObtenerMantenimientosReportesUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-012');

        $mantenimientos = $useCase->mantenimientosVencidos();
        $paginas = ReportePaginador::chunkParaPdf($mantenimientos);

        return Pdf::view('reports.activos.mantenimientos-vencidos', array_merge(HotelInfo::getBaseData(), [
            'paginas' => $paginas,
            'totalRegistros' => $mantenimientos->count(),
        ]))->name('HTB-ACT-012-Mantenimientos-Vencidos.pdf')->download();
    }

    // ─── HTB-ACT-013: Hoja de Habitación / Espacio ─────────────────────────

    public function hojaHabitacionPdf(string $tipo, int $id, ObtenerHojaHabitacionEspacioUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-013');

        $data = $useCase->ejecutar($tipo, $id);

        return Pdf::view('reports.activos.hoja-habitacion', array_merge(HotelInfo::getBaseData(), [
            'entidad' => $data['entidad'],
            'activos' => $data['activos'],
            'tipo' => $tipo,
        ]))->name("HTB-ACT-013-Hoja-{$tipo}-{$id}.pdf")->download();
    }
}
