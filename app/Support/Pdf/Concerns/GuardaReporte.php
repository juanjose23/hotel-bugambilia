<?php

declare(strict_types=1);

namespace App\Support\Pdf\Concerns;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Storage;

trait GuardaReporte
{
    /**
     * @param  array<string, mixed>  $parametros
     */
    public function guardarAuditoria(
        string $tipoReporte,
        array $parametros = [],
        ?PDF $pdf = null,
    ): ?string {
        $rutaArchivo = null;

        if ($pdf !== null) {
            $filename = str_replace('/', '-', $tipoReporte).'_'.now()->format('Ymd_His').'.pdf';
            $rutaArchivo = 'reports/'.$filename;
            Storage::disk('public')->put($rutaArchivo, $pdf->output());
        }

        app(RegistrarAuditoriaReporte::class)->ejecutar(
            tipoReporte: $tipoReporte,
            parametros: $parametros,
            rutaArchivo: $rutaArchivo,
        );

        return $rutaArchivo;
    }
}
