<?php

declare(strict_types=1);

namespace App\Interactors\Reportes;

use App\Repository\Persistencia\Shared\ReporteRepositorioInterface;

final readonly class RegistrarAuditoriaReporte
{
    public function __construct(
        private ReporteRepositorioInterface $reporteRepositorio,
    ) {}

    /**
     * @param  array<string, mixed>  $parametros
     */
    public function ejecutar(string $tipoReporte, array $parametros = [], ?string $rutaArchivo = null, ?int $usuarioId = null): void
    {
        $this->reporteRepositorio->registrarAuditoria([
            'usuario_id' => $usuarioId ?? auth()->id(),
            'tipo_reporte' => $tipoReporte,
            'parametros' => json_encode($parametros),
            'ruta_archivo' => $rutaArchivo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
