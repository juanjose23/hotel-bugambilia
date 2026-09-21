<?php

declare(strict_types=1);

namespace App\Jobs;

use App\BusinessLogic\Shared\Reportes\ReporteDispatcher;
use App\Events\Shared\ReporteGenerado;
use App\Events\Shared\ReporteIniciado;
use App\Notifications\Reportes\Shared\NotificadorReportes;
use App\Repository\Queries\Usuarios\BuscarUsuarioCuentaQuery;
use App\Support\Pdf\Concerns\GuardaReporte;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

final class GenerarReporteJob implements ShouldQueue
{
    use GuardaReporte, Queueable;

    /** Número máximo de intentos antes de marcar el job como fallido. */
    public int $tries = 2;

    /** Tiempo máximo (segundos) para generar un reporte masivo. */
    public int $timeout = 600;

    /** @var array<int, int> Segundos de espera entre reintentos para no saturar el worker. */
    public array $backoff = [60, 120];

    /** @param array<string, mixed> $parametros */
    public function __construct(
        public string $codigoReporte,
        public array $parametros,
        public int $usuarioId,
    ) {}

    public function handle(): void
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(600);

        if ($this->usuarioId > 0) {
            ReporteIniciado::dispatch(
                $this->usuarioId,
                $this->codigoReporte,
            );
        }

        $dispatcher = app(ReporteDispatcher::class);
        $pdf = $dispatcher->generar($this->codigoReporte, $this->parametros);

        $rutaArchivo = $this->guardarAuditoria(
            tipoReporte: $this->codigoReporte,
            parametros: $this->parametros,
            pdf: $pdf,
            usuarioId: $this->usuarioId,
        );

        if ($rutaArchivo === null) {
            return;
        }

        $urlDescarga = Storage::disk('public')->url($rutaArchivo);

        if ($this->usuarioId > 0) {
            ReporteGenerado::dispatch(
                $this->usuarioId,
                $this->codigoReporte,
                $urlDescarga,
            );
        }
    }

    public function failed(?\Throwable $exception): void
    {
        if ($this->usuarioId <= 0) {
            return;
        }

        try {
            $usuario = app(BuscarUsuarioCuentaQuery::class)->porId($this->usuarioId);
            if ($usuario !== null) {
                app(NotificadorReportes::class)->reporteFallido(
                    $usuario,
                    $this->codigoReporte,
                    $exception?->getMessage() ?? 'Error inesperado al generar el documento.',
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
