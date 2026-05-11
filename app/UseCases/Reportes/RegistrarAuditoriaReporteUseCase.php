<?php

namespace App\UseCases\Reportes;

use Illuminate\Support\Facades\DB;

class RegistrarAuditoriaReporteUseCase
{
    /**
     * Registra un nuevo evento de auditoría cada vez que se genera un reporte.
     *
     * @param  array<string, mixed>  $parametros
     */
    public function ejecutar(string $tipoReporte, array $parametros = [], ?string $rutaArchivo = null): void
    {
        $usuarioId = auth()->id();

        // En PostgreSQL no se puede comparar columnas JSON con '=' directamente.
        // Cambiamos a INSERT simple para llevar un log histórico completo de cada descarga.
        DB::table('auditoria_reportes')->insert([
            'usuario_id' => $usuarioId,
            'tipo_reporte' => $tipoReporte,
            'parametros' => json_encode($parametros),
            'ruta_archivo' => $rutaArchivo,
            'conteo_descargas' => 1, // Cada registro es 1 descarga
            'ultima_descarga_en' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
