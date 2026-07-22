<?php

declare(strict_types=1);

namespace App\Interactors\Reportes;

use Illuminate\Support\Facades\DB;

final class RegistrarAuditoriaReporte
{
    /** @param array<string, mixed> $parametros */
    public function ejecutar(string $tipoReporte, array $parametros = [], ?string $rutaArchivo = null): void
    {
        $usuarioId = auth()->id();

        DB::table('auditoria_reportes')->insert([
            'usuario_id' => $usuarioId,
            'tipo_reporte' => $tipoReporte,
            'parametros' => json_encode($parametros),
            'ruta_archivo' => $rutaArchivo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @param array<string, mixed> $parametros */
    public function execute(string $tipoReporte, array $parametros = [], ?string $rutaArchivo = null): void
    {
        $this->ejecutar($tipoReporte, $parametros, $rutaArchivo);
    }
}
