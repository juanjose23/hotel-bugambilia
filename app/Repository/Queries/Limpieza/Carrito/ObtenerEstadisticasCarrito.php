<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\BusinessLogic\Limpieza\Data\CarritoEstadisticasData;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use Illuminate\Support\Facades\DB;

class ObtenerEstadisticasCarrito
{
    public function execute(int $carritoId, ?int $colaboradorActualId = null): CarritoEstadisticasData
    {
        $ejecucionActiva = LimpiezaEjecucion::with('colaborador.persona.personaNatural')
            ->where('carrito_id', $carritoId)
            ->where(function ($query): void {
                $query
                    ->where('estado', EstadoLimpieza::EnProgreso)
                    ->orWhere(function ($pendiente): void {
                        $pendiente
                            ->where('estado', EstadoLimpieza::Pendiente)
                            ->whereDate('fecha', now()->toDateString());
                    });
            })
            ->first();

        $stockStats = DB::table((new Stock)->getTable())
            ->where('ubicacion_id', $carritoId)
            ->where('cantidad', '>', 0)
            ->selectRaw('COUNT(*) as total_items, COALESCE(SUM(cantidad), 0) as total_cantidad')
            ->first();

        $totalItems = $stockStats ? (int) $stockStats->total_items : 0;
        $totalCantidad = $stockStats ? (float) $stockStats->total_cantidad : 0.0;

        $ultimoAbastecimiento = MovimientoStock::with(['creadoPor.persona.personaNatural', 'ubicacionOrigen'])
            ->where('ubicacion_destino_id', $carritoId)
            ->where('tipo', 'TRASLADO')
            ->latest()
            ->first();

        $totalMovimientos = MovimientoStock::where(function ($q) use ($carritoId) {
            $q->where('ubicacion_origen_id', $carritoId)
                ->orWhere('ubicacion_destino_id', $carritoId);
        })->count();

        $bloqueado = $ejecucionActiva !== null;

        $esAsignado = false;
        $nombreColaborador = null;

        if ($ejecucionActiva && $ejecucionActiva->colaborador_id) {
            $esAsignado = $colaboradorActualId === $ejecucionActiva->colaborador_id;

            $persona = $ejecucionActiva->colaborador?->persona;
            if ($persona) {
                $pn = $persona->personaNatural;
                $nombreColaborador = trim(
                    ($persona->primer_nombre ?? '')
                    .' '.($persona->segundo_nombre ?? '')
                    .' '.($pn->primer_apellido ?? '')
                    .' '.($pn->segundo_apellido ?? '')
                );
            }
        }

        return new CarritoEstadisticasData(
            totalItems: $totalItems,
            totalCantidad: $totalCantidad,
            totalMovimientos: $totalMovimientos,
            bloqueado: $bloqueado,
            esAsignado: $esAsignado,
            nombreColaborador: $nombreColaborador ?: null,
            ejecucionActiva: $ejecucionActiva,
            ultimoAbastecimiento: $ultimoAbastecimiento,
        );
    }
}
