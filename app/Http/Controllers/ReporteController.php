<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\GenerarReporteJob;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class ReporteController extends Controller
{
    protected function streamPdf(PDF $pdf, string $filename): StreamedResponse
    {
        return response()->stream(fn () => print ($pdf->output()), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "filename=\"{$filename}\"",
        ]);
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
}
