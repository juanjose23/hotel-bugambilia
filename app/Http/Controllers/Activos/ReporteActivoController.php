<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activos;

use App\Http\Controllers\ReporteController;
use App\Interactors\Activos\Reportes\GenerarReporteActivo;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReporteActivoController extends ReporteController
{
    public function __construct(
        private readonly GenerarReporteActivo $generarReporteActivo,
    ) {}

    public function inventarioGeneralPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('inventario_general', $request->all());
        }

        $pdf = $this->generarPdf('inventarioGeneralPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-ACT-001-Inventario-General.pdf');
    }

    public function inventarioGeneralExcel(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('inventario_general_excel', $request->all());
        }

        $result = $this->generarReporteActivo->execute('inventarioGeneralExcel', $request->all());

        if (! $result instanceof StreamedResponse) {
            throw new \UnexpectedValueException("El reporte 'inventarioGeneralExcel' no generó un archivo Excel.");
        }

        return $result;
    }

    public function fichaActivoPdf(Request $request, Activo $activo): StreamedResponse
    {
        $params = array_merge($request->all(), ['activo' => $activo]);

        $pdf = $this->generarPdf('fichaActivoPdf', $params);

        return $this->streamPdf($pdf, 'HTB-ACT-002-Ficha-Activo.pdf');
    }

    public function fichaMantenimientoPdf(Request $request, ActivoMantenimiento $mantenimiento): StreamedResponse
    {
        $params = array_merge($request->all(), ['mantenimiento' => $mantenimiento]);

        $pdf = $this->generarPdf('fichaMantenimientoPdf', $params);

        return $this->streamPdf($pdf, 'HTB-ACT-003-Ficha-Mantenimiento.pdf');
    }

    public function etiquetasPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('etiquetas', $request->all());
        }

        $pdf = $this->generarPdf('etiquetasPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-ACT-004-Etiquetas-Codigos-Barras.pdf');
    }

    public function porUbicacionPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('por_ubicacion', $request->all());
        }

        $pdf = $this->generarPdf('porUbicacionPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-ACT-005-Activos-por-Ubicacion.pdf');
    }

    public function historialMovimientosPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('historial', $request->all());
        }

        $pdf = $this->generarPdf('historialMovimientosPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-ACT-006-Historial-Movimientos.pdf');
    }

    public function enMantenimientoPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('en_mantenimiento', $request->all());
        }

        $pdf = $this->generarPdf('enMantenimientoPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-ACT-007-Activos-en-Mantenimiento.pdf');
    }

    public function garantiasProximasPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('garantias', $request->all());
        }

        $pdf = $this->generarPdf('garantiasProximasPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-ACT-008-Garantias-Proximas.pdf');
    }

    public function dadosDeBajaPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('bajas', $request->all());
        }

        $pdf = $this->generarPdf('dadosDeBajaPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-ACT-009-Activos-Dados-de-Baja.pdf');
    }

    public function extraviadosPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('extraviados', $request->all());
        }

        $pdf = $this->generarPdf('extraviadosPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-ACT-010-Activos-Extraviados.pdf');
    }

    public function sinAsignacionPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('sin_asignacion', $request->all());
        }

        $pdf = $this->generarPdf('sinAsignacionPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-ACT-011-Activos-Sin-Asignacion.pdf');
    }

    public function mantenimientosVencidosPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('manttos_vencidos', $request->all());
        }

        $pdf = $this->generarPdf('mantenimientosVencidosPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-ACT-012-Mantenimientos-Vencidos.pdf');
    }

    public function hojaHabitacionPdf(Request $request, string $tipo = 'habitacion', int $id = 0): StreamedResponse|RedirectResponse
    {
        $routeTipo = $request->route('tipo');
        $routeId = $request->route('id');

        $resolvedTipo = $tipo !== '' ? $tipo : (is_string($routeTipo) ? $routeTipo : 'habitacion');
        $resolvedId = $id > 0 ? $id : (is_numeric($routeId) ? (int) $routeId : 0);

        $params = array_merge($request->all(), [
            'tipo' => $resolvedTipo,
            'id' => $resolvedId,
        ]);

        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('hoja_habitacion', $params);
        }

        $pdf = $this->generarPdf('hojaHabitacionPdf', $params);

        return $this->streamPdf($pdf, 'HTB-ACT-013-Hoja-Habitacion.pdf');
    }

    /** @param array<string, mixed> $params */
    private function generarPdf(string $reportName, array $params = []): PDF
    {
        $result = $this->generarReporteActivo->execute($reportName, $params);

        if (! $result instanceof PDF) {
            throw new \UnexpectedValueException("El reporte '{$reportName}' no generó un PDF.");
        }

        return $result;
    }
}
