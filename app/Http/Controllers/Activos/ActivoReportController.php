<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activos;

use App\Exports\Activos\ActivosExport;
use App\Http\Controllers\Controller;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoMantenimiento;
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

    private function auditoria(string $codigo, mixed $record = null): void
    {
        app(RegistrarAuditoriaReporteUseCase::class)->ejecutar($codigo, [
            'usuario' => auth()->id(),
            'ip' => request()->ip(),
            'referencia_id' => $record?->id,
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

        return Pdf::view('reports.activos.inventario-general', array_merge($this->baseData(), [
            'activos' => $useCase->inventarioGeneral($filtros),
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

        return Pdf::view('reports.activos.activo', array_merge($this->baseData(), [
            'record' => $useCase->fichaActivo($activo),
        ]))->name("HTB-ACT-002-Ficha-Activo-{$activo->codigo_inventario}.pdf")->download();
    }

    // ─── HTB-ACT-003: Ficha de Mantenimiento ──────────────────────────────

    public function fichaMantenimientoPdf(ActivoMantenimiento $mantenimiento, ObtenerFichasReportesUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-003', $mantenimiento);

        return Pdf::view('reports.activos.mantenimiento', array_merge($this->baseData(), [
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

        return Pdf::view('reports.catalogos.etiquetas-codigos-barras', array_merge($this->baseData(), [
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

        $tipoFiltro = request('ubicacion_tipo');

        return Pdf::view('reports.activos.por-ubicacion', array_merge($this->baseData(), [
            'ubicaciones' => $useCase->ejecutar($tipoFiltro),
        ]))->name('HTB-ACT-005-Activos-por-Ubicacion.pdf')->download();
    }

    // ─── HTB-ACT-006: Historial de Movimientos de Activo ────────────────────

    public function historialMovimientosPdf(ObtenerHistorialMovimientosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-006');

        $activoId = (int) request('activo_id');
        $data = $useCase->ejecutar($activoId);

        return Pdf::view('reports.activos.historial-movimientos', array_merge($this->baseData(), [
            'activo' => $data['activo'],
            'lineaTiempo' => $data['lineaTiempo'],
            'filtroActivo' => $activoId > 0 ? $data['activo'] : null,
        ]))->name("HTB-ACT-006-Historial-{$data['activo']->codigo_inventario}.pdf")->download();
    }

    // ─── HTB-ACT-007: Activos en Mantenimiento ─────────────────────────────

    public function enMantenimientoPdf(ObtenerMantenimientosReportesUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-007');

        return Pdf::view('reports.activos.en-mantenimiento', array_merge($this->baseData(), [
            'activos' => $useCase->enMantenimiento(),
        ]))->name('HTB-ACT-007-Activos-en-Mantenimiento.pdf')->download();
    }

    // ─── HTB-ACT-008: Garantías Próximas a Vencer ──────────────────────────

    public function garantiasProximasPdf(ObtenerReportesActivosVariosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-008');

        $dias = (int) request('dias', 90);

        return Pdf::view('reports.activos.garantias-proximas', array_merge($this->baseData(), [
            'activos' => $useCase->garantiasProximas($dias),
            'dias' => $dias,
        ]))->name('HTB-ACT-008-Garantias-Proximas.pdf')->download();
    }

    // ─── HTB-ACT-009: Activos Dados de Baja ────────────────────────────────

    public function dadosDeBajaPdf(ObtenerReportesActivosVariosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-009');

        return Pdf::view('reports.activos.dados-de-baja', array_merge($this->baseData(), [
            'bajas' => $useCase->dadosDeBaja(),
        ]))->name('HTB-ACT-009-Activos-Dados-de-Baja.pdf')->download();
    }

    // ─── HTB-ACT-010: Activos Extraviados ──────────────────────────────────

    public function extraviadosPdf(ObtenerReportesActivosVariosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-010');

        return Pdf::view('reports.activos.extraviados', array_merge($this->baseData(), [
            'activos' => $useCase->extraviados(),
        ]))->name('HTB-ACT-010-Activos-Extraviados.pdf')->download();
    }

    // ─── HTB-ACT-011: Activos Sin Asignación ───────────────────────────────

    public function sinAsignacionPdf(ObtenerReportesActivosVariosUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-011');

        return Pdf::view('reports.activos.sin-asignacion', array_merge($this->baseData(), [
            'activos' => $useCase->sinAsignacion(),
        ]))->name('HTB-ACT-011-Activos-Sin-Asignacion.pdf')->download();
    }

    // ─── HTB-ACT-012: Mantenimientos Vencidos ──────────────────────────────

    public function mantenimientosVencidosPdf(ObtenerMantenimientosReportesUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-012');

        return Pdf::view('reports.activos.mantenimientos-vencidos', array_merge($this->baseData(), [
            'mantenimientos' => $useCase->mantenimientosVencidos(),
        ]))->name('HTB-ACT-012-Mantenimientos-Vencidos.pdf')->download();
    }

    // ─── HTB-ACT-013: Hoja de Habitación / Espacio ─────────────────────────

    public function hojaHabitacionPdf(string $tipo, int $id, ObtenerHojaHabitacionEspacioUseCase $useCase): PdfBuilder
    {
        $this->auditoria('HTB-ACT-013');

        $data = $useCase->ejecutar($tipo, $id);

        return Pdf::view('reports.activos.hoja-habitacion', array_merge($this->baseData(), [
            'entidad' => $data['entidad'],
            'activos' => $data['activos'],
            'tipo' => $tipo,
        ]))->name("HTB-ACT-013-Hoja-{$tipo}-{$id}.pdf")->download();
    }
}
