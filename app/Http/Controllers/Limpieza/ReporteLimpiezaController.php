<?php

declare(strict_types=1);

namespace App\Http\Controllers\Limpieza;

use App\Actions\Limpieza\Reportes\GenerarReporteOperacionHoteleraAction;
use App\Http\Controllers\ReporteController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReporteLimpiezaController extends ReporteController
{
    public function __construct(
        private readonly GenerarReporteOperacionHoteleraAction $reporteOperacionHotelera,
    ) {}

    public function operacionHoteleraPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->generarPdf($request, 'operacion_hotelera', 'HTB-LIM-001-Limpieza-Operacion-Hotelera.pdf');
    }

    public function operacionHoteleraPreview(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->previsualizarPdf($request, 'admin.limpieza.reportes.operacion-hotelera.pdf', 'Reporte de Limpieza y Operación Hotelera', 'operacion_hotelera');
    }

    public function tiempoPromedioPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->generarPdf($request, 'tiempo_promedio_limpieza', 'HTB-LIM-002-Tiempo-Promedio-Limpieza.pdf');
    }

    public function tiempoPromedioPreview(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->previsualizarPdf($request, 'admin.limpieza.reportes.tiempo-promedio.pdf', 'Tiempo Promedio de Limpieza', 'tiempo_promedio_limpieza');
    }

    public function pendientesBloqueadasPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->generarPdf($request, 'habitaciones_pendientes_bloqueadas', 'HTB-LIM-003-Habitaciones-Pendientes-Bloqueadas.pdf');
    }

    public function pendientesBloqueadasPreview(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->previsualizarPdf($request, 'admin.limpieza.reportes.pendientes-bloqueadas.pdf', 'Habitaciones Pendientes y Bloqueadas', 'habitaciones_pendientes_bloqueadas');
    }

    public function amenitiesHabitacionPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->generarPdf($request, 'consumo_amenities_habitacion', 'HTB-LIM-004-Consumo-Amenities-Habitacion.pdf');
    }

    public function amenitiesHabitacionPreview(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->previsualizarPdf($request, 'admin.limpieza.reportes.amenities-habitacion.pdf', 'Consumo de Amenities por Habitación', 'consumo_amenities_habitacion');
    }

    public function productividadPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->generarPdf($request, 'productividad_colaborador_turno', 'HTB-LIM-005-Productividad-Colaborador-Turno.pdf');
    }

    public function productividadPreview(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->previsualizarPdf($request, 'admin.limpieza.reportes.productividad.pdf', 'Productividad por Colaborador y Turno', 'productividad_colaborador_turno');
    }

    private function previsualizarPdf(Request $request, string $rutaPdf, string $titulo, string $reporte): Response|StreamedResponse|JsonResponse
    {
        abort_unless($this->puedeGenerarReporte(), 403);

        $params = array_merge($request->all(), ['reporte' => $reporte]);

        return $this->manejarReporte(
            $request,
            $reporte,
            $params,
            fn (): Response => response()->view('reports.layout.pdf-preview', [
                'titulo' => $titulo,
                'pdfUrl' => route($rutaPdf, $request->query()),
            ]),
        );
    }

    private function generarPdf(Request $request, string $reporte, string $archivo): Response|StreamedResponse|JsonResponse
    {
        abort_unless($this->puedeGenerarReporte(), 403);

        return $this->manejarReporte(
            $request,
            $reporte,
            array_merge($request->all(), ['reporte' => $reporte]),
            fn (): StreamedResponse => $this->streamPdf(
                $this->reporteOperacionHotelera->pdf(array_merge($request->all(), ['reporte' => $reporte])),
                $archivo,
            ),
        );
    }

    private function puedeGenerarReporte(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
        $roleName = is_string($superAdminRole) ? $superAdminRole : 'super_admin';

        return $user->is_admin === true
            || $user->hasRole($roleName)
            || $user->can('Limpieza:ReporteOperacionHotelera')
            || $user->can('page_ReportesLimpieza');
    }
}
