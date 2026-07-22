<?php

declare(strict_types=1);

namespace App\Interactors\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Repository\Models\Activos\Activo;
use App\Repository\Persistencia\Activos\ActivoAsignacionRepositorioInterface;
use App\Repository\Persistencia\Activos\ActivoRepositorioInterface;
use Illuminate\Support\Facades\DB;

class AsignarActivo
{
    public function __construct(
        private readonly ActivoRepositorioInterface $activoRepositorio,
        private readonly ActivoAsignacionRepositorioInterface $asignacionRepositorio,
    ) {}

    public function ejecutar(int $activoId, string $asignableType, int $asignableId, int $userId, ?string $motivo = null): void
    {
        $activo = $this->loadActivo($activoId);

        $this->assertPuedeAsignar($activo);

        DB::transaction(function () use ($activo, $asignableType, $asignableId, $userId, $motivo) {
            $this->cerrarAsignacionAnterior($activo);

            $this->crearNuevaAsignacion(
                activo: $activo,
                asignableType: $asignableType,
                asignableId: $asignableId,
                userId: $userId,
                motivo: $motivo,
            );
        });
    }

    private function loadActivo(int $id): Activo
    {
        $activo = $this->activoRepositorio->buscarPorId($id);

        if (! $activo) {
            throw new \RuntimeException("No se encontró el activo con ID {$id}");
        }

        return $activo;
    }

    private function assertPuedeAsignar(Activo $activo): void
    {
        if ($activo->estado === EstadoActivo::DadoDeBaja) {
            throw new \RuntimeException('No se puede asignar un activo dado de baja.');
        }
    }

    private function cerrarAsignacionAnterior(Activo $activo): void
    {
        $this->asignacionRepositorio->cerrarAsignacionesVigentes(
            activoId: $activo->id,
            fechaFin: now()->toDateString(),
            estado: EstadoAsignacion::Cerrada->value
        );
    }

    private function crearNuevaAsignacion(
        Activo $activo,
        string $asignableType,
        int $asignableId,
        int $userId,
        ?string $motivo,
    ): void {
        $this->asignacionRepositorio->crear([
            'activo_id' => $activo->id,
            'asignable_type' => $asignableType,
            'asignable_id' => $asignableId,
            'fecha_inicio' => now()->toDateString(),
            'motivo' => $motivo ?: 'Traslado y reasignación física de activo',
            'asignado_por_id' => $userId,
            'estado' => EstadoAsignacion::Vigente,
        ]);
    }
}
