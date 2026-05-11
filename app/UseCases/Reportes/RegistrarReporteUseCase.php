<?php

namespace App\UseCases\Reportes;

use App\Models\Audits\AuditoriaReporte;

class RegistrarReporteUseCase
{
    /**
     * Registra un evento de generación de reporte.
     *
     * @param  array<string, mixed>  $parametros
     */
    public function registrar(string $tipo, array $parametros = [], ?string $rutaArchivo = null, ?int $usuarioId = null): AuditoriaReporte
    {
        return AuditoriaReporte::create([
            'usuario_id' => $usuarioId,
            'tipo_reporte' => $tipo,
            'parametros' => $parametros ?: null,
            'ruta_archivo' => $rutaArchivo,
        ]);
    }

    public function incrementarDescarga(int $id): ?AuditoriaReporte
    {
        $registro = AuditoriaReporte::find($id);
        if (! $registro) {
            return null;
        }

        $registro->increment('conteo_descargas');
        $registro->ultima_descarga_en = now();
        $registro->save();

        return $registro;
    }
}
