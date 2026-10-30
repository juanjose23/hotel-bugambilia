<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\GenerarReporteJob;
use Barryvdh\DomPDF\PDF;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class ReporteController extends Controller
{
    protected function streamPdf(PDF $pdf, string $filename): Response
    {
        return $pdf->stream($filename);
    }

    /**
     * Genera y transmite el reporte en tiempo real (stream directo).
     *
     * @param  array<string, mixed>  $params
     * @param  Closure(): (Response|StreamedResponse)  $generadorInline  Closure que produce el PDF inline.
     */
    protected function manejarReporte(
        Request $request,
        string $codigoReporte,
        array $params,
        Closure $generadorInline,
    ): Response|StreamedResponse|JsonResponse {
        return $generadorInline();
    }

    /** @param array<string, mixed> $params */
    protected function despacharEnSegundoPlano(string $reportCode, array $params = []): RedirectResponse
    {
        GenerarReporteJob::dispatch(
            codigoReporte: $reportCode,
            parametros: $params,
            usuarioId: (int) (auth()->id() ?? 0),
        );

        return back()->with('status', 'El reporte se esta generando. Recibiras una notificacion cuando este listo.');
    }

    protected function fechaRequest(Request $request, string $campo, string $porDefecto): string
    {
        $valor = $request->input($campo);

        return is_string($valor) && $valor !== '' ? $valor : $porDefecto;
    }

    protected function textoRequest(Request $request, string $campo): ?string
    {
        $valor = $request->input($campo);

        return is_string($valor) && $valor !== '' ? $valor : null;
    }
}
