<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Events\Limpieza\FaltanteReposicionDetectado;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Shared\Stock as SharedStock;
use App\Repository\Queries\Limpieza\Carrito\ObtenerCarritoAsignado;
use Illuminate\Support\Facades\DB;

class ProcesarOperacionLimpieza
{
    public function __construct(
        private readonly ObtenerCarritoAsignado $obtenerCarritoAsignado,
        private readonly ProcesarBlancosLimpieza $procesarBlancos,
        private readonly ProcesarConsumosLimpieza $procesarConsumos,
        private readonly ProcesarInsumosLimpieza $procesarInsumos,
        private readonly ProcesarAdicionalesLimpieza $procesarAdicionales,
        private readonly ProcesarSustitucionesLimpieza $procesarSustituciones,
    ) {}

    /** @param array<string, mixed> $data */
    public function ejecutar(int $ejecucionId, array $data, ?int $usuarioId = null): void
    {
        $usuarioId = $usuarioId ?: (auth()->id() !== null ? (int) auth()->id() : null);

        $missingItems = [];

        DB::transaction(function () use ($ejecucionId, $data, $usuarioId, &$missingItems) {
            $ejecucion = LimpiezaEjecucion::where('id', $ejecucionId)->lockForUpdate()->firstOrFail();

            $tipoDestino = match ($ejecucion->limpiable_type) {
                Habitacion::class => 'habitacion',
                Espacio::class => 'espacio',
                Ubicacion::class => 'ubicacion',
                default => throw new \InvalidArgumentException('Tipo de limpiable inválido'),
            };

            $carritoId = $ejecucion->carrito_id;
            if (! $carritoId && $ejecucion->colaborador_id) {
                $carrito = $this->obtenerCarritoAsignado->execute((int) $ejecucion->colaborador_id, $ejecucion->fecha->toDateString());
                $carritoId = $carrito?->id;
            }

            if ($carritoId) {
                InventarioStock::where('ubicacion_id', $carritoId)->lockForUpdate()->get();
            }

            $missingItems = $this->procesarBlancos->ejecutar($ejecucion, $data, $carritoId, $tipoDestino, $usuarioId);
            $this->procesarConsumos->ejecutar($ejecucion, $data, $usuarioId, $carritoId, $tipoDestino);

            if ($carritoId) {
                $this->procesarInsumos->ejecutar($ejecucion, $data, $carritoId, $usuarioId);
            }

            $this->procesarAdicionales->ejecutar($ejecucion, $data, $carritoId, $tipoDestino, $usuarioId);
            $this->procesarSustituciones->ejecutar($ejecucion, $data, $carritoId, $usuarioId);

            $hasDiscrepancies = false;
            $allStocks = SharedStock::where('stockable_type', $ejecucion->limpiable_type)
                ->where('stockable_id', $ejecucion->limpiable_id)
                ->get();

            foreach ($allStocks as $st) {
                if ((float) $st->cantidad_actual < (float) $st->cantidad_ideal) {
                    $hasDiscrepancies = true;
                    break;
                }
            }

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

            $ejecucion->update([
                'estado' => $nuevoEstado,
                'hora_fin' => now()->format('H:i:s'),
                'detalles_checklist' => $checklist,
                'observaciones' => $data['observaciones'] ?? null,
                'consumos' => $data['consumos_cantidad'] ?? null,
            ]);

            $limpiable = $ejecucion->limpiable;
            if ($limpiable instanceof Habitacion) {
                $prevEstado = $ejecucion->estado_previo !== null
                    ? EstadoEspacio::fromValue($ejecucion->estado_previo)
                    : EstadoEspacio::DISPONIBLE;

                $limpiable->update([
                    'estado' => in_array($prevEstado, [EstadoEspacio::Ocupada, EstadoEspacio::Mantenimiento], true)
                        ? $prevEstado
                        : EstadoEspacio::DISPONIBLE,
                ]);
            } elseif ($limpiable instanceof Espacio) {
                $limpiable->update([
                    'estado' => EstadoEspacio::Disponible,
                ]);
            }

            if ($ejecucion->solicitud) {
                $ejecucion->solicitud->update([
                    'estado' => EstadoLimpieza::Completada,
                ]);
            }
        });

        if (! empty($missingItems)) {
            $ejecucion = LimpiezaEjecucion::find($ejecucionId);
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
