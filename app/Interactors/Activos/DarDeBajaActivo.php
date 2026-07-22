<?php

declare(strict_types=1);

namespace App\Interactors\Activos;

use App\Actions\Activos\CerrarAsignacionActivaAction;
use App\Actions\Activos\DecrementarStockActivoAction;
use App\Actions\Activos\RegistrarBajaAction;
use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\TipoBaja;
use App\Events\Activos\ActivoBajaFallida;
use App\Events\Activos\ActivoDadoDeBaja;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Persistencia\Activos\ActivoRepositorioInterface;
use App\Repository\Queries\Catalogos\ObtenerUbicacionAlmacen;
use Illuminate\Support\Facades\DB;

class DarDeBajaActivo
{
    public function __construct(
        private readonly CerrarAsignacionActivaAction $cerrarAsignacion,
        private readonly RegistrarBajaAction $registrarBaja,
        private readonly DecrementarStockActivoAction $decrementarStock,
        private readonly ActivoRepositorioInterface $activoRepositorio,
        private readonly ObtenerUbicacionAlmacen $obtenerAlmacen,
    ) {}

    public function execute(
        int $activoId,
        TipoBaja $motivoTipo,
        string $motivoDetalle,
        int $userId,
        ?float $valorResidual = null,
        ?int $aprobadoPorId = null,
        ?string $documentoSoporte = null
    ): void {
        try {
            $activo = $this->activoRepositorio->buscarPorId($activoId);

            if (! $activo) {
                throw new \RuntimeException("No se encontró el activo con ID {$activoId}");
            }

            if ($activo->estado === EstadoActivo::DadoDeBaja) {
                throw new \RuntimeException('Este activo ya está dado de baja.');
            }

            DB::transaction(function () use ($activo, $motivoTipo, $motivoDetalle, $userId, $valorResidual, $aprobadoPorId, $documentoSoporte) {
                $ultimaAsignacion = $this->cerrarAsignacion->ejecutar($activo);

                $ubicacionId = null;
                if ($ultimaAsignacion && $ultimaAsignacion->asignable_type === Ubicacion::class) {
                    $ubicacionId = $ultimaAsignacion->asignable_id;
                }

                if (! $ubicacionId) {
                    $ubicacionBodega = $this->obtenerAlmacen->ejecutar();
                    $ubicacionId = $ubicacionBodega?->id;
                }

                if (! $ubicacionId) {
                    throw new \RuntimeException('No existe ninguna ubicación activa en el sistema.');
                }

                $baja = $this->registrarBaja->ejecutar(
                    $activo,
                    $motivoTipo,
                    $motivoDetalle,
                    $userId,
                    $valorResidual,
                    $aprobadoPorId,
                    $documentoSoporte
                );

                $referencia = "Baja definitiva de activo {$activo->codigo_inventario} - {$motivoTipo->label()}";
                $this->decrementarStock->ejecutar($activo, $ubicacionId, $baja, $userId, $referencia);
            });

            event(new ActivoDadoDeBaja($activo));
        } catch (\Throwable $e) {
            event(new ActivoBajaFallida($activoId, $e));
            throw $e;
        }
    }
}
