<?php

declare(strict_types=1);

namespace App\Interactors\Activos\Mantenimiento;

use App\Actions\Activos\CerrarAsignacionActivaAction;
use App\Actions\Activos\CrearAsignacionTallerAction;
use App\Actions\Activos\RegistrarMantenimientoAction;
use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\TipoMantenimiento;
use App\Events\Activos\ActivoEnviadoMantenimiento;
use App\Events\Activos\ActivoMantenimientoFallido;
use App\Repository\Persistencia\Activos\ActivoRepositorioInterface;
use App\Repository\Queries\Catalogos\ObtenerUbicacionTaller;
use Illuminate\Support\Facades\DB;

class EnviarAMantenimiento
{
    public function __construct(
        private readonly CerrarAsignacionActivaAction $cerrarAsignacion,
        private readonly CrearAsignacionTallerAction $crearAsignacionTaller,
        private readonly RegistrarMantenimientoAction $registrarMantenimiento,
        private readonly ActivoRepositorioInterface $activoRepositorio,
        private readonly ObtenerUbicacionTaller $obtenerTaller,
    ) {}

    public function execute(
        int $activoId,
        TipoMantenimiento $tipo,
        string $descripcion,
        int $userId,
        ?float $costo = null,
        ?int $monedaId = null,
        ?int $proveedorId = null,
        ?string $notes = null
    ): void {
        try {
            $activo = $this->activoRepositorio->buscarPorId($activoId);

            if (! $activo) {
                throw new \RuntimeException("No se encontró el activo con ID {$activoId}");
            }

            if ($activo->estado === EstadoActivo::DadoDeBaja) {
                throw new \RuntimeException('No se puede enviar a mantenimiento un activo dado de baja.');
            }

            $ubicacionTaller = $this->obtenerTaller->ejecutar();

            if (! $ubicacionTaller) {
                throw new \RuntimeException('No existe ninguna ubicación activa en el sistema.');
            }

            DB::transaction(function () use ($activo, $tipo, $descripcion, $userId, $costo, $monedaId, $proveedorId, $notes, $ubicacionTaller) {
                $this->cerrarAsignacion->ejecutar($activo);

                $motivo = "Ingreso a taller de mantenimiento ({$tipo->label()})";
                $this->crearAsignacionTaller->ejecutar($activo, $ubicacionTaller, $userId, $motivo);

                $this->registrarMantenimiento->ejecutar(
                    $activo,
                    $tipo,
                    $descripcion,
                    $userId,
                    $costo,
                    $monedaId,
                    $proveedorId,
                    $notes
                );
            });

            event(new ActivoEnviadoMantenimiento($activo));
        } catch (\Throwable $e) {
            event(new ActivoMantenimientoFallido($activoId, $e));
            throw $e;
        }
    }
}
