<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\ReporteController;
use App\Interactors\Inventario\Reportes\GenerarReporteInventario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReporteInventarioController extends ReporteController
{
    public function __construct(
        private readonly GenerarReporteInventario $generarReporteInventario,
    ) {}

    public function stockPorProductoPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-INV-001',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('stockPorProductoPdf', $request->all()),
                'HTB-INV-001-Stock-Producto.pdf',
            ),
        );
    }

    public function stockPorProductoExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('stockPorProductoExcel', $request->all());
    }

    public function movimientosPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-INV-002',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('movimientosPdf', $request->all()),
                'HTB-INV-002-Movimientos.pdf',
            ),
        );
    }

    public function movimientosExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('movimientosExcel', $request->all());
    }

    public function cuarentenaPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-INV-004',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('cuarentenaPdf', $request->all()),
                'HTB-INV-004-Cuarentena.pdf',
            ),
        );
    }

    public function cuarentenaExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('cuarentenaExcel', $request->all());
    }

    public function proximosVencerPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-INV-005',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('proximosVencerPdf', $request->all()),
                'HTB-INV-005-Proximos-Vencer.pdf',
            ),
        );
    }

    public function proximosVencerExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('proximosVencerExcel', $request->all());
    }

    public function mermasPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-INV-006',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('mermasPdf', $request->all()),
                'HTB-INV-006-Mermas.pdf',
            ),
        );
    }

    public function mermasExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('mermasExcel', $request->all());
    }

    public function valorizacionPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-INV-007',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('valorizacionPdf', $request->all()),
                'HTB-INV-007-Valorizacion.pdf',
            ),
        );
    }

    public function valorizacionExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('valorizacionExcel', $request->all());
    }

    public function rotacionPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-INV-008',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('rotacionPdf', $request->all()),
                'HTB-INV-008-Rotacion.pdf',
            ),
        );
    }

    public function rotacionExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('rotacionExcel', $request->all());
    }

    public function trazabilidadLotePdf(Request $request, int $loteId): Response|StreamedResponse|JsonResponse
    {
        $params = array_merge($request->all(), ['lote_id' => $loteId]);

        return $this->manejarReporte(
            $request,
            'HTB-INV-011',
            $params,
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('trazabilidadLotePdf', $params),
                "HTB-INV-011-Trazabilidad-Lote-{$loteId}.pdf",
            ),
        );
    }

    public function vencidosPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-INV-012',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('vencidosPdf', $request->all()),
                'HTB-INV-012-Lotes-Vencidos.pdf',
            ),
        );
    }

    public function vencidosExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('vencidosExcel', $request->all());
    }

    public function mermasTotalesExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('mermasExcel', $request->all());
    }

    public function stockMinimoPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-INV-009',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('stockMinimoPdf', $request->all()),
                'HTB-INV-009-Stock-Minimo.pdf',
            ),
        );
    }

    public function stockMinimoExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('stockMinimoExcel', $request->all());
    }

    public function ajustesPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-INV-010',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('ajustesPdf', $request->all()),
                'HTB-INV-010-Ajustes-Inventario.pdf',
            ),
        );
    }

    public function ajustesExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('ajustesExcel', $request->all());
    }

    public function costoVentasPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        return $this->manejarReporte(
            $request,
            'HTB-INV-013',
            $request->all(),
            fn () => $this->streamPdf(
                $this->generarReporteInventario->execute('costoVentasPdf', $request->all()),
                'HTB-INV-013-Costo-Ventas.pdf',
            ),
        );
    }

    public function costoVentasExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('costoVentasExcel', $request->all());
    }
}
