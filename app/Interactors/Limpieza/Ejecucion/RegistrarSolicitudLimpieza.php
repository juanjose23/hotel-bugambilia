<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Ejecucion;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Persistencia\Habitaciones\HabitacionRepositorioInterface;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final readonly class RegistrarSolicitudLimpieza
{
    public function __construct(
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
        private HabitacionRepositorioInterface $habitacionRepositorio,
    ) {}

    public function execute(
        mixed $limpiable,
        ?int $limpiableId = null,
        string $prioridad = 'normal',
        ?string $notas = null,
    ): SolicitudLimpieza {
        return $this->ejecutar($limpiable, $limpiableId, $prioridad, $notas);
    }

    public function ejecutar(
        mixed $limpiable,
        ?int $limpiableId = null,
        string $prioridad = 'normal',
        ?string $notas = null,
    ): SolicitudLimpieza {
        return DB::transaction(function () use ($limpiable, $limpiableId, $prioridad, $notas): SolicitudLimpieza {
            if ($limpiable instanceof Model) {
                $instance = $limpiable;
                $modelClass = get_class($instance);
                $rawKey = $instance->getKey();
                $modelId = is_numeric($rawKey) ? (int) $rawKey : 0;
            } elseif (is_string($limpiable) && $limpiableId !== null) {
                $modelClass = $limpiable;
                $modelId = $limpiableId;
                if ($modelClass === Habitacion::class) {
                    $instance = $this->habitacionRepositorio->buscarPorIdConLock($modelId);
                } else {
                    $instance = $this->limpiezaRepositorio->buscarLimpiablePorTipoYId($modelClass, $modelId);
                }
            } else {
                assert(is_numeric($limpiable), 'El valor limpiable debe ser numérico en este contexto.');
                $modelId = (int) $limpiable;
                $modelClass = Habitacion::class;
                $instance = $this->habitacionRepositorio->buscarPorIdConLock($modelId);
            }

            if ($instance instanceof Habitacion) {
                $this->habitacionRepositorio->actualizarEstado($instance, EstadoEspacio::SUCIA);
            }

            $solicitudExistente = $this->limpiezaRepositorio->buscarSolicitudActivaPorLimpiable($modelClass, $modelId);
            if ($solicitudExistente !== null) {
                return $solicitudExistente;
            }

            $solicitud = $this->limpiezaRepositorio->crearSolicitud([
                'limpiable_type' => $modelClass,
                'limpiable_id' => $modelId,
                'prioridad' => $prioridad,
                'estado' => EstadoLimpieza::Pendiente,
                'notas' => $notas,
            ]);

            $ubicacion = null;
            if ($instance instanceof Ubicacion) {
                $ubicacion = $instance;
            } elseif ($instance instanceof Habitacion || $instance instanceof Espacio) {
                $instance->loadMissing('ubicacion');
                $ubicacion = $instance->ubicacion;
            }

            $turno = null;
            if ($instance instanceof Espacio || $modelClass === Espacio::class) {
                $turno = $this->limpiezaRepositorio->buscarTurnoRestaurante();
            }

            if (! $turno && $ubicacion) {
                $turno = $this->limpiezaRepositorio->buscarTurnoPorUbicacion((int) $ubicacion->id);
            }

            if (! $turno) {
                $turno = $this->limpiezaRepositorio->buscarTurnoDefault();
            }

            $this->limpiezaRepositorio->crearEjecucion([
                'solicitud_id' => $solicitud->id,
                'limpiable_type' => $modelClass,
                'limpiable_id' => $modelId,
                'turno_id' => $turno->id,
                'colaborador_id' => null,
                'fecha' => Carbon::now()->toDateString(),
                'estado' => EstadoLimpieza::Pendiente,
            ]);

            return $solicitud;
        });
    }
}
