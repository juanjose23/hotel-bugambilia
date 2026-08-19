<?php

declare(strict_types=1);

namespace App\Support\Pdf\Concerns;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

trait GuardaReporte
{
    /**
     * @param  array<string, mixed>  $parametros
     */
    public function guardarAuditoria(
        string $tipoReporte,
        array $parametros = [],
        mixed $pdf = null,
        ?int $usuarioId = null,
    ): ?string {
        $rutaArchivo = null;

        if ($pdf !== null) {
            $filename = str_replace('/', '-', $tipoReporte).'_'.now()->format('Ymd_His').'.pdf';
            $rutaArchivo = 'reports/'.$filename;

            $content = match (true) {
                $pdf instanceof PDF => $pdf->output(),
                $pdf instanceof Response => $pdf->getContent(),
                default => throw new \InvalidArgumentException('Formato de PDF no soportado para auditoría.'),
            };

            if (is_string($content)) {
                Storage::disk('public')->put($rutaArchivo, $content);
            }
        }

        app(RegistrarAuditoriaReporte::class)->ejecutar(
            tipoReporte: $tipoReporte,
            parametros: $parametros,
            rutaArchivo: $rutaArchivo,
            usuarioId: $usuarioId,
        );

        return $rutaArchivo;
    }
}
