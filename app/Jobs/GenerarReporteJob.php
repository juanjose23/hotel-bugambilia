<?php

declare(strict_types=1);

namespace App\Jobs;

use App\BusinessLogic\Shared\Reportes\ReporteDispatcher;
use App\Events\Shared\ReporteGenerado;
use App\Support\Pdf\Concerns\GuardaReporte;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

final class GenerarReporteJob implements ShouldQueue
{
    use GuardaReporte;
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    /** @param array<string, mixed> $parametros */
    public function __construct(
        public string $codigoReporte,
        public array $parametros,
        public int $usuarioId,
    ) {}

    public function handle(): void
    {
        $dispatcher = app(ReporteDispatcher::class);
        $pdf = $dispatcher->generar($this->codigoReporte, $this->parametros);

        $rutaArchivo = $this->guardarAuditoria(
            tipoReporte: $this->codigoReporte,
            parametros: $this->parametros,
            pdf: $pdf,
        );

        $urlDescarga = $rutaArchivo !== null
            ? Storage::disk('public')->url($rutaArchivo)
            : null;

        ReporteGenerado::dispatch(
            $this->usuarioId,
            $this->codigoReporte,
            $urlDescarga,
        );
    }
}
