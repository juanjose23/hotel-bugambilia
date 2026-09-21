<?php

declare(strict_types=1);

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\ReporteController;
use App\Interactors\Compras\Reportes\GenerarReporteCompra;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReporteCompraController extends ReporteController
{
    public function __construct(
        private readonly GenerarReporteCompra $generarReporteCompra,
    ) {}

    public function imprimirCotizacion(Cotizacion $cotizacion): StreamedResponse
    {
        $pdf = $this->generarReporteCompra->execute('cotizacion', ['cotizacion' => $cotizacion]);

        return $this->streamPdf($pdf, 'HTB-COM-002-Cotizacion.pdf');
    }

    public function imprimirComparativa(Solicitud $solicitud): StreamedResponse
    {
        $pdf = $this->generarReporteCompra->execute('comparativa', ['solicitud' => $solicitud]);

        return $this->streamPdf($pdf, 'HTB-COM-006-Comparativa.pdf');
    }

    public function imprimirSolicitud(Solicitud $solicitud): StreamedResponse
    {
        $pdf = $this->generarReporteCompra->execute('solicitud', ['solicitud' => $solicitud]);

        return $this->streamPdf($pdf, 'HTB-COM-001-'.$solicitud->codigo.'.pdf');
    }

    public function imprimirOrdenCompra(OrdenCompra $ordenCompra): StreamedResponse
    {
        $pdf = $this->generarReporteCompra->execute('orden_compra', ['orden' => $ordenCompra]);

        return $this->streamPdf($pdf, 'HTB-COM-003-'.$ordenCompra->codigo.'.pdf');
    }

    public function imprimirRecepcion(RecepcionCompra $recepcion): StreamedResponse
    {
        $pdf = $this->generarReporteCompra->execute('recepcion', ['recepcion' => $recepcion]);

        return $this->streamPdf($pdf, 'HTB-COM-004-'.$recepcion->codigo.'.pdf');
    }

    public function devolucion(DevolucionCompra $devolucion): StreamedResponse
    {
        $pdf = $this->generarReporteCompra->execute('devolucion', ['devolucion' => $devolucion]);

        return $this->streamPdf($pdf, 'HTB-COM-005-'.$devolucion->codigo.'.pdf');
    }

    public function imprimirResumenDepartamentos(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-COM-017',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteCompra->execute('resumen_departamentos', $request->all()),
                'HTB-COM-017-Resumen-Compras-Departamentos.pdf',
            ),
        );
    }

    public function rotacion(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-COM-007',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteCompra->execute('rotacion_compras', $request->all()),
                'HTB-COM-007-Rotacion-Compras.pdf',
            ),
        );
    }

    public function tiemposEntrega(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-COM-008',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteCompra->execute('tiempos_entrega', $request->all()),
                'HTB-COM-008-Tiempos-Entrega.pdf',
            ),
        );
    }

    public function solicitudesEstado(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-COM-010',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteCompra->execute('solicitudes_estado', $request->all()),
                'HTB-COM-010-Solicitudes-Estado.pdf',
            ),
        );
    }

    public function seguimientoOc(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-COM-011',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteCompra->execute('seguimiento_oc', $request->all()),
                'HTB-COM-011-Seguimiento-OC.pdf',
            ),
        );
    }

    public function recepcionesPorProveedor(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-COM-012',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteCompra->execute('recepciones_proveedor', $request->all()),
                'HTB-COM-012-Recepciones-Proveedor.pdf',
            ),
        );
    }

    public function analisisPrecio(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-COM-013',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteCompra->execute('analisis_precio', $request->all()),
                'HTB-COM-013-Analisis-Precio.pdf',
            ),
        );
    }

    public function valorizacion(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-COM-014',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteCompra->execute('valorizacion_categoria', $request->all()),
                'HTB-COM-014-Valorizacion.pdf',
            ),
        );
    }

    public function rankingProveedores(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-COM-015',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteCompra->execute('ranking_proveedores', $request->all()),
                'HTB-COM-015-Ranking-Proveedores.pdf',
            ),
        );
    }

    public function devoluciones(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-COM-016',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteCompra->execute('devoluciones_proveedor', $request->all()),
                'HTB-COM-016-Devoluciones.pdf',
            ),
        );
    }

    public function trazabilidadCompleta(Solicitud $solicitud): StreamedResponse
    {
        $pdf = $this->generarReporteCompra->execute('trazabilidad_completa', ['solicitud' => $solicitud]);

        return $this->streamPdf($pdf, 'HTB-COM-009-Trazabilidad-'.$solicitud->codigo.'.pdf');
    }
}
