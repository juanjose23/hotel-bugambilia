<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activos;

use App\Http\Controllers\ReporteController;
use App\Interactors\Activos\Reportes\GenerarReporteActivo;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReporteActivoController extends ReporteController
{
    public function __construct(
        private readonly GenerarReporteActivo $generarReporteActivo,
    ) {}

    public function inventarioGeneralPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-ACT-001',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarPdf('inventarioGeneralPdf', $request->all()),
                'HTB-ACT-001-Inventario-General.pdf',
            ),
        );
    }

    public function inventarioGeneralExcel(Request $request): StreamedResponse
    {
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

    public function etiquetasPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-ACT-004',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarPdf('etiquetasPdf', $request->all()),
                'HTB-ACT-004-Etiquetas.pdf',
            ),
        );
    }

    public function porUbicacionPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-ACT-005',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarPdf('porUbicacionPdf', $request->all()),
                'HTB-ACT-005-Activos-por-Ubicacion.pdf',
            ),
        );
    }

    public function historialMovimientosPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-ACT-006',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarPdf('historialMovimientosPdf', $request->all()),
                'HTB-ACT-006-Historial-Movimientos.pdf',
            ),
        );
    }

    public function enMantenimientoPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-ACT-007',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarPdf('enMantenimientoPdf', $request->all()),
                'HTB-ACT-007-Activos-en-Mantenimiento.pdf',
            ),
        );
    }

    public function garantiasProximasPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-ACT-008',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarPdf('garantiasProximasPdf', $request->all()),
                'HTB-ACT-008-Garantias-Proximas.pdf',
            ),
        );
    }

    public function dadosDeBajaPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-ACT-009',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarPdf('dadosDeBajaPdf', $request->all()),
                'HTB-ACT-009-Activos-Dados-de-Baja.pdf',
            ),
        );
    }

    public function extraviadosPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-ACT-010',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarPdf('extraviadosPdf', $request->all()),
                'HTB-ACT-010-Activos-Extraviados.pdf',
            ),
        );
    }

    public function sinAsignacionPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-ACT-011',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarPdf('sinAsignacionPdf', $request->all()),
                'HTB-ACT-011-Activos-Sin-Asignacion.pdf',
            ),
        );
    }

    public function mantenimientosVencidosPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-ACT-012',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarPdf('mantenimientosVencidosPdf', $request->all()),
                'HTB-ACT-012-Mantenimientos-Vencidos.pdf',
            ),
        );
    }

    public function hojaHabitacionPdf(Request $request, string $tipo = 'habitacion', int $id = 0): Response|StreamedResponse|JsonResponse
    {
        $routeTipo = $request->route('tipo');
        $routeId = $request->route('id');

        $resolvedTipo = $tipo !== '' ? $tipo : (is_string($routeTipo) ? $routeTipo : 'habitacion');
        $resolvedId = $id > 0 ? $id : (is_numeric($routeId) ? (int) $routeId : 0);

        $params = array_merge($request->all(), [
            'tipo' => $resolvedTipo,
            'id' => $resolvedId,
        ]);

        return $this->manejarReporte(
            $request,
            'HTB-ACT-013',
            $params,
            fn () => $this->streamPdf(
                $this->generarPdf('hojaHabitacionPdf', $params),
                'HTB-ACT-013-Hoja-Habitacion.pdf',
            ),
        );
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
