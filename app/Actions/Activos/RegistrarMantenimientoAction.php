<?php

declare(strict_types=1);

namespace App\Actions\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Persistencia\Activos\ActivoMantenimientoRepositorioInterface;
use App\Repository\Persistencia\Activos\ActivoRepositorioInterface;

class RegistrarMantenimientoAction
{
    public function __construct(
        private readonly ActivoMantenimientoRepositorioInterface $mantenimientoRepositorio,
        private readonly ActivoRepositorioInterface $activoRepositorio,
    ) {}

    public function ejecutar(
        Activo $activo,
        TipoMantenimiento $tipo,
        string $descripcion,
        int $userId,
        ?float $costo = null,
        ?int $monedaId = null,
        ?int $proveedorId = null,
        ?string $notes = null
    ): ActivoMantenimiento {
        $activo->estado = EstadoActivo::EnMantenimiento;
        $this->activoRepositorio->guardar($activo);

        return $this->mantenimientoRepositorio->crear([
            'activo_id' => $activo->id,
            'tipo' => $tipo,
            'fecha_programada' => now()->toDateString(),
            'descripcion' => $descripcion,
            'costo' => $costo,
            'moneda_id' => $monedaId,
            'proveedor_id' => $proveedorId,
            'realizado_por_id' => $userId,
            'estado' => EstadoMantenimiento::EnProceso,
            'notes' => $notes,
        ]);
    }
}
