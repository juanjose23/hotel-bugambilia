<?php

declare(strict_types=1);

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Interactors\Compras\Reportes\GenerarReporteCompra;
use App\Jobs\GenerarReporteJob;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReporteCompraController extends Controller
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

    public function imprimirResumenDepartamentos(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('resumen_departamentos', $request->all());
        }

        $pdf = $this->generarReporteCompra->execute('resumen_departamentos', $request->all());

        return $this->streamPdf($pdf, 'Resumen-Compras-Departamentos.pdf');
    }

    public function rotacion(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('rotacion', $request->all());
        }

        $pdf = $this->generarReporteCompra->execute('rotacion_compras', $request->all());

        return $this->streamPdf($pdf, 'HTB-COM-007-Rotacion-Compras.pdf');
    }

    public function tiemposEntrega(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('tiempos_entrega', $request->all());
        }

        $pdf = $this->generarReporteCompra->execute('tiempos_entrega', $request->all());

        return $this->streamPdf($pdf, 'HTB-COM-008-Tiempos-Entrega.pdf');
    }

    public function solicitudesEstado(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('solicitudes_estado', $request->all());
        }

        $pdf = $this->generarReporteCompra->execute('solicitudes_estado', $request->all());

        return $this->streamPdf($pdf, 'HTB-COM-010-Solicitudes-Estado.pdf');
    }

    public function seguimientoOc(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('seguimiento_oc', $request->all());
        }

        $pdf = $this->generarReporteCompra->execute('seguimiento_oc', $request->all());

        return $this->streamPdf($pdf, 'HTB-COM-011-Seguimiento-OC.pdf');
    }

    public function recepcionesPorProveedor(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('recepciones_proveedor', $request->all());
        }

        $pdf = $this->generarReporteCompra->execute('recepciones_proveedor', $request->all());

        return $this->streamPdf($pdf, 'HTB-COM-012-Recepciones-Proveedor.pdf');
    }

    public function analisisPrecio(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('analisis_precio', $request->all());
        }

        $pdf = $this->generarReporteCompra->execute('analisis_precio', $request->all());

        return $this->streamPdf($pdf, 'HTB-COM-013-Analisis-Precio.pdf');
    }

    public function valorizacion(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('valorizacion', $request->all());
        }

        $pdf = $this->generarReporteCompra->execute('valorizacion_categoria', $request->all());

        return $this->streamPdf($pdf, 'HTB-COM-014-Valorizacion.pdf');
    }

    public function rankingProveedores(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('ranking_proveedores', $request->all());
        }

        $pdf = $this->generarReporteCompra->execute('ranking_proveedores', $request->all());

        return $this->streamPdf($pdf, 'HTB-COM-015-Ranking-Proveedores.pdf');
    }

    public function devoluciones(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('devoluciones', $request->all());
        }

        $pdf = $this->generarReporteCompra->execute('devoluciones_proveedor', $request->all());

        return $this->streamPdf($pdf, 'HTB-COM-016-Devoluciones.pdf');
    }

    public function trazabilidadCompleta(Solicitud $solicitud): StreamedResponse
    {
        $pdf = $this->generarReporteCompra->execute('trazabilidad_completa', ['solicitud' => $solicitud]);

        return $this->streamPdf($pdf, 'HTB-COM-009-Trazabilidad-'.$solicitud->codigo.'.pdf');
    }

    private function streamPdf(PDF $pdf, string $filename): StreamedResponse
    {
        return response()->stream(fn () => print ($pdf->output()), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "filename=\"{$filename}\"",
        ]);
    }

    /** @param array<string, mixed> $params */
    private function despacharEnSegundoPlano(string $reportCode, array $params = []): RedirectResponse
    {
        GenerarReporteJob::dispatch(
            codigoReporte: $reportCode,
            parametros: $params,
            usuarioId: (int) (auth()->id() ?? 0),
        );

        return back()->with('status', 'El reporte se esta generando. Recibiras una notificacion cuando este listo.');
    }
}
