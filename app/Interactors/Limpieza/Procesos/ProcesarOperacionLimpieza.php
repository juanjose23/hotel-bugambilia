<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Events\Limpieza\FaltanteReposicionDetectado;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use App\Repository\Queries\Limpieza\Carrito\ObtenerCarritoAsignado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class ProcesarOperacionLimpieza
{
    public function __construct(
        private ObtenerCarritoAsignado $obtenerCarritoAsignado,
        private ProcesarBlancosLimpieza $procesarBlancos,
        private ProcesarConsumosLimpieza $procesarConsumos,
        private ProcesarInsumosLimpieza $procesarInsumos,
        private ProcesarAdicionalesLimpieza $procesarAdicionales,
        private ProcesarSustitucionesLimpieza $procesarSustituciones,
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(int $ejecucionId, array $data, ?int $usuarioId = null): void
    {
        $this->ejecutar($ejecucionId, $data, $usuarioId);
    }

    /** @param array<string, mixed> $data */
    public function ejecutar(int $ejecucionId, array $data, ?int $usuarioId = null): void
    {
        $missingItems = [];

        DB::transaction(function () use ($ejecucionId, $data, $usuarioId, &$missingItems): void {
            $ejecucion = $this->limpiezaRepositorio->buscarEjecucionPorIdConLock($ejecucionId);

            $tipoDestino = match ($ejecucion->limpiable_type) {
                Habitacion::class => 'habitacion',
                Espacio::class => 'espacio',
                Ubicacion::class => 'ubicacion',
                default => throw new InvalidArgumentException('Tipo de limpiable inválido'),
            };

            $carritoId = $ejecucion->carrito_id;
            if (! $carritoId && $ejecucion->colaborador_id) {
                $fechaStr = $ejecucion->fecha->format('Y-m-d');

                $carrito = $this->obtenerCarritoAsignado->execute((int) $ejecucion->colaborador_id, $fechaStr);
                $carritoId = $carrito?->id;
            }

            $missingItems = $this->procesarBlancos->ejecutar($ejecucion, $data, $carritoId ? (int) $carritoId : null, $tipoDestino, $usuarioId);
            $this->procesarConsumos->ejecutar($ejecucion, $data, $usuarioId, $carritoId ? (int) $carritoId : null, $tipoDestino);

            if ($carritoId) {
                $this->procesarInsumos->ejecutar($ejecucion, $data, (int) $carritoId, $usuarioId);
            }

            $this->procesarAdicionales->ejecutar($ejecucion, $data, $carritoId ? (int) $carritoId : null, $tipoDestino, $usuarioId);
            $this->procesarSustituciones->ejecutar($ejecucion, $data, $carritoId ? (int) $carritoId : null, $usuarioId);

            $hasDiscrepancies = $this->limpiezaRepositorio->tieneDiscrepanciasSharedStock(
                $ejecucion->limpiable_type,
                (int) $ejecucion->limpiable_id
            );

            $checklist = $data['checklist'] ?? [];
            if (! is_iterable($checklist)) {
                $checklist = [];
            }
            foreach ($checklist as $completed) {
                if (! $completed) {
                    $hasDiscrepancies = true;
                    break;
                }
            }

            $nuevoEstado = $hasDiscrepancies
                ? EstadoLimpieza::CompletadaConDiscrepancia
                : EstadoLimpieza::Completada;

            $this->limpiezaRepositorio->actualizarEjecucion($ejecucion, [
                'estado' => $nuevoEstado,
                'hora_fin' => Carbon::now()->format('H:i:s'),
                'detalles_checklist' => $checklist,
                'observaciones' => $data['observaciones'] ?? null,
                'consumos' => $data['consumos_cantidad'] ?? null,
            ]);

            $limpiable = $ejecucion->limpiable;
            if ($limpiable instanceof Habitacion) {
                $prevEstado = $ejecucion->estado_previo !== null
                    ? EstadoEspacio::fromValue($ejecucion->estado_previo)
                    : EstadoEspacio::DISPONIBLE;

                $this->limpiezaRepositorio->actualizarEstadoLimpiable(
                    $limpiable,
                    in_array($prevEstado, [EstadoEspacio::Ocupada, EstadoEspacio::Mantenimiento], true)
                        ? $prevEstado
                        : EstadoEspacio::DISPONIBLE
                );
            } elseif ($limpiable instanceof Espacio) {
                $this->limpiezaRepositorio->actualizarEstadoLimpiable($limpiable, EstadoEspacio::Disponible);
            }

            if ($ejecucion->solicitud) {
                $this->limpiezaRepositorio->actualizarSolicitud($ejecucion->solicitud, [
                    'estado' => EstadoLimpieza::Completada,
                ]);
            }
        });

        if (! empty($missingItems)) {
            $ejecucion = $this->limpiezaRepositorio->buscarEjecucionPorId($ejecucionId);
            if ($ejecucion && $ejecucion->colaborador) {
                $user = $ejecucion->colaborador->persona?->user;
                if ($user) {
                    event(new FaltanteReposicionDetectado(
                        ejecucion: $ejecucion,
                        items: $missingItems,
                        destinatario: $user,
                    ));
                }
            }
        }
    }
}
