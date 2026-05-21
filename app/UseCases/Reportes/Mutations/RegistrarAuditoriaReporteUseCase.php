<?php

namespace App\UseCases\Reportes\Mutations;

use Illuminate\Support\Facades\DB;

class RegistrarAuditoriaReporteUseCase
{
    /**
     * @param  array<string, mixed>  $parametros
     */
    public function ejecutar(string $tipoReporte, array $parametros = [], ?string $rutaArchivo = null): void
    {
        $usuarioId = auth()->id();

        DB::table('auditoria_reportes')->insert([
            'usuario_id' => $usuarioId,
            'tipo_reporte' => $tipoReporte,
            'parametros' => json_encode($parametros),
            'ruta_archivo' => $rutaArchivo,
            'conteo_descargas' => 1,
            'ultima_descarga_en' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
