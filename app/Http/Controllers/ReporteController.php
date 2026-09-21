<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\BusinessLogic\Shared\Reportes\ContarRegistrosReporte;
use App\Jobs\GenerarReporteJob;
use App\Repository\Models\User;
use Barryvdh\DomPDF\PDF;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    /**
     * Genera el reporte de forma inteligente:
     * - Si el número de registros supera el umbral configurado → despacha Job y retorna JSON 202.
     * - Si está dentro del límite o el parámetro modo=directo → genera el PDF inline.
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
        $contador = app(ContarRegistrosReporte::class);

        // Modo directo forzado por el cliente (sin umbral)
        if ($request->boolean('directo')) {
            return $generadorInline();
        }

        if ($contador->superaUmbral($codigoReporte, $params)) {
            $this->despacharJobInterno($codigoReporte, $params);

            /** @var User|null $usuario */
            $usuario = auth()->user();
            if ($usuario !== null) {
                Notification::make()
                    ->title('⏳ Generando reporte en segundo plano')
                    ->body("El reporte {$codigoReporte} se está procesando debido al alto volumen de registros. Recibirás un aviso con el enlace de descarga tan pronto finalice.")
                    ->warning()
                    ->sendToDatabase($usuario);
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'en_segundo_plano' => true,
                    'mensaje' => 'El reporte tiene demasiados registros para generarse en tiempo real. Se está procesando en segundo plano y recibirás una notificación cuando esté listo para descargar.',
                    'codigo' => $codigoReporte,
                ], 202);
            }

            return response()->view('reports.layout.segundo-plano', [
                'codigo' => $codigoReporte,
                'mensaje' => 'El reporte tiene demasiados registros para generarse en tiempo real. Se está procesando en segundo plano y recibirás una notificación en el panel cuando esté listo para descargar.',
            ], 202);
        }

        return $generadorInline();
    }

    /** @param array<string, mixed> $params */
    private function despacharJobInterno(string $reportCode, array $params): void
    {
        GenerarReporteJob::dispatch(
            codigoReporte: $reportCode,
            parametros: $params,
            usuarioId: (int) (auth()->id() ?? 0),
        );
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
