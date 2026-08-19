<?php

declare(strict_types=1);

namespace App\Interactors\Reportes;

use Illuminate\Support\Facades\DB;

final class RegistrarAuditoriaReporte
{
    /**
     * @param  array<string, mixed>  $parametros
     */
    public function ejecutar(string $tipoReporte, array $parametros = [], ?string $rutaArchivo = null, ?int $usuarioId = null): void
    {
        DB::table('auditoria_reportes')->insert([
            'usuario_id' => $usuarioId ?? auth()->id(),
            'tipo_reporte' => $tipoReporte,
            'parametros' => json_encode($parametros),
            'ruta_archivo' => $rutaArchivo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
