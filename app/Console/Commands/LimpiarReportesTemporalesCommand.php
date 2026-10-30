<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class LimpiarReportesTemporalesCommand extends Command
{
    protected $signature = 'reportes:limpiar-temporales
                            {--minutos= : Tiempo de expiración en minutos (opcional)}
                            {--all : Eliminar todos los reportes temporales y obsoletos sin importar su antigüedad}';

    protected $description = 'Elimina los archivos de reportes PDF temporales y obsoletos en temp-reportes y public/reports para no saturar el almacenamiento.';

    public function handle(): int
    {
        $minutosOption = $this->option('minutos');
        $forzarTodos = (bool) $this->option('all');
        $configTtl = config('reportes.token_ttl_minutos');
        $minutos = is_numeric($minutosOption)
            ? (int) $minutosOption
            : (is_int($configTtl) ? $configTtl : 30);

        /** @var array<int, string> $directorios */
        $directorios = array_values(array_unique(array_filter([
            storage_path('app/temp-reportes'),
            Storage::disk('public')->path('reports'),
            storage_path('app/public/reports'),
            storage_path('app/reports'),
        ])));

        $segundosLimite = ($forzarTodos || $minutos <= 0) ? 0 : ($minutos * 60);
        $ahora = time();
        $eliminados = 0;
        $bytesLiberados = 0;

        foreach ($directorios as $directorio) {
            if (! is_dir($directorio)) {
                continue;
            }

            $archivos = glob("{$directorio}/*.pdf") ?: [];
            foreach ($archivos as $archivo) {
                $mtime = filemtime($archivo);
                if ($mtime !== false && ($segundosLimite === 0 || ($ahora - $mtime) > $segundosLimite)) {
                    $tamano = filesize($archivo) ?: 0;
                    if (@unlink($archivo)) {
                        $eliminados++;
                        $bytesLiberados += $tamano;
                    }
                }
            }
        }

        $mbLiberados = round($bytesLiberados / (1024 * 1024), 2);
        $condicion = $segundosLimite === 0
            ? 'todos los archivos'
            : "expiración: >{$minutos} min";

        $this->info("Limpieza completada: {$eliminados} archivos PDF eliminados en reports y temp-reportes ({$mbLiberados} MB liberados, {$condicion}).");

        return self::SUCCESS;
    }
}
