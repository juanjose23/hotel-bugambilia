<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\ReporteController;
use App\Interactors\Inventario\Reportes\GenerarReporteInventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReporteInventarioController extends ReporteController
{
    public function __construct(
        private readonly GenerarReporteInventario $generarReporteInventario,
    ) {}

    public function stockPorProductoPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('stock', $request->all());
        }

        $pdf = $this->generarReporteInventario->execute('stockPorProductoPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-INV-001-Stock-Producto.pdf');
    }

    public function stockPorProductoExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('stockPorProductoExcel', $request->all());
    }

    public function movimientosPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('movimientos', $request->all());
        }

        $pdf = $this->generarReporteInventario->execute('movimientosPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-INV-003-Movimientos.pdf');
    }

    public function movimientosExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('movimientosExcel', $request->all());
    }

    public function cuarentenaPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('cuarentena', $request->all());
        }

        $pdf = $this->generarReporteInventario->execute('cuarentenaPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-INV-004-Cuarentena.pdf');
    }

    public function cuarentenaExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('cuarentenaExcel', $request->all());
    }

    public function proximosVencerPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('proximos_vencer', $request->all());
        }

        $pdf = $this->generarReporteInventario->execute('proximosVencerPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-INV-005-Proximos-Vencer.pdf');
    }

    public function proximosVencerExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('proximosVencerExcel', $request->all());
    }

    public function mermasPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('mermas', $request->all());
        }

        $pdf = $this->generarReporteInventario->execute('mermasPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-INV-006-Mermas.pdf');
    }

    public function mermasExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('mermasExcel', $request->all());
    }

    public function valorizacionPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('valorizacion', $request->all());
        }

        $pdf = $this->generarReporteInventario->execute('valorizacionPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-INV-007-Valorizacion.pdf');
    }

    public function valorizacionExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('valorizacionExcel', $request->all());
    }

    public function rotacionPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('rotacion', $request->all());
        }

        $pdf = $this->generarReporteInventario->execute('rotacionPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-INV-008-Rotacion.pdf');
    }

    public function rotacionExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('rotacionExcel', $request->all());
    }

    public function trazabilidadLotePdf(Request $request, int $loteId): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('trazabilidad_lote', array_merge($request->all(), ['lote_id' => $loteId]));
        }

        $pdf = $this->generarReporteInventario->execute('trazabilidadLotePdf', array_merge($request->all(), ['lote_id' => $loteId]));

        return $this->streamPdf($pdf, "HTB-INV-011-Trazabilidad-Lote-{$loteId}.pdf");
    }

    public function vencidosPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('vencidos', $request->all());
        }

        $pdf = $this->generarReporteInventario->execute('vencidosPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-INV-012-Lotes-Vencidos.pdf');
    }

    public function vencidosExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('vencidosExcel', $request->all());
    }

    public function mermasTotalesExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('mermasExcel', $request->all());
    }

    public function stockMinimoPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('stock_minimo', $request->all());
        }

        $pdf = $this->generarReporteInventario->execute('stockMinimoPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-INV-009-Stock-Minimo.pdf');
    }

    public function stockMinimoExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('stockMinimoExcel', $request->all());
    }

    public function ajustesPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('ajustes', $request->all());
        }

        $pdf = $this->generarReporteInventario->execute('ajustesPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-INV-010-Ajustes-Inventario.pdf');
    }

    public function ajustesExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('ajustesExcel', $request->all());
    }

    public function costoVentasPdf(Request $request): StreamedResponse|RedirectResponse
    {
        if ($request->boolean('background')) {
            return $this->despacharEnSegundoPlano('costo_ventas', $request->all());
        }

        $pdf = $this->generarReporteInventario->execute('costoVentasPdf', $request->all());

        return $this->streamPdf($pdf, 'HTB-INV-013-Costo-Ventas.pdf');
    }

    public function costoVentasExcel(Request $request): StreamedResponse
    {
        return $this->generarReporteInventario->executeExcel('costoVentasExcel', $request->all());
    }
}
